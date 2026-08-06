{
  description = "Laravel Blog — Nix devShell (macOS Apple Silicon)";

  inputs = {
    nixpkgs.url = "github:nixos/nixpkgs/nixos-unstable";
    flake-utils.url = "github:numtide/flake-utils";
  };

  outputs =
    { self, nixpkgs, flake-utils }:
    flake-utils.lib.eachDefaultSystem (
      system:
      let
        pkgs = import nixpkgs {
          inherit system;
          config.allowUnfree = false;
        };

        # ── PHP 8.4 with Laravel-required extensions ──────────────────
        php = pkgs.php84.buildEnv {
          extensions =
            { all, enabled }:
            # Only add extensions NOT already in the default enabled set
            with all;
            enabled ++ [
              pdo_pgsql
              pgsql
            ];
          extraConfig = ''
            memory_limit = 256M
            max_execution_time = 0
            upload_max_filesize = 20M
            post_max_size = 20M
            date.timezone = Asia/Shanghai
            opcache.enable = 1
            opcache.memory_consumption = 128
            opcache.max_accelerated_files = 10000
          '';
        };

        # ── Composer 2 (PHP 8.4) ─────────────────────────────────────
        composer = pkgs.php84Packages.composer;

        # ── Node.js (current LTS) ────────────────────────────────────
        nodejs = pkgs.nodejs_22;

        # ── Human-readable system label ──────────────────────────────
        sysLabel = {
          aarch64-darwin = "macOS (ARM)";
          x86_64-darwin  = "macOS (Intel)";
          x86_64-linux   = "Linux (x64)";
          aarch64-linux  = "Linux (ARM)";
        }.${system} or system;

      in
      {
        devShells = {

          # ── 默认：全栈开发（PHP + Node + PostgreSQL）────────────────
          default = pkgs.mkShell {
            buildInputs = with pkgs; [
              php composer nodejs postgresql_16
              git curl wget jq ripgrep concurrently
              phpactor                          # PHP LSP (Emacs eglot 补全/跳转/重构)
              pyright                           # Python LSP
              typescript-language-server         # JS/TS LSP
            ];

            APP_ENV = "local";
            SYSTEM_LABEL = sysLabel;

            # ── Proxy (ClashX GFW) ─────────────────────────────────────
            http_proxy  = "http://127.0.0.1:7890";
            https_proxy = "http://127.0.0.1:7890";
            HTTP_PROXY  = "http://127.0.0.1:7890";
            HTTPS_PROXY = "http://127.0.0.1:7890";
            all_proxy   = "socks5://127.0.0.1:7890";
            ALL_PROXY   = "socks5://127.0.0.1:7890";
            no_proxy    = "localhost,127.0.0.1,::1";
            NO_PROXY    = "localhost,127.0.0.1,::1";

            shellHook = ''
              echo "[nix] Proxy: http://127.0.0.1:7890 (ClashX)"

              # ── PostgreSQL 本地服务 ──────────────────────────────────
              PGDATA="$PWD/storage/db/pgdata"
              PGLOG="$PWD/storage/logs/pg.log"
              if command -v pg_ctl &>/dev/null; then
                if [ ! -d "$PGDATA" ]; then
                  echo "[nix] Initializing PostgreSQL..."
                  initdb -D "$PGDATA" --encoding=UTF8 --locale=C --auth=trust > /dev/null 2>&1
                fi
                if ! pg_isready -q 2>/dev/null; then
                  echo "[nix] Starting PostgreSQL..."
                  # 在子 shell 中启动并关闭全部继承 fd(3+)：否则 postmaster 会
                  # 持有 direnv 输出管道的写端，导致 `direnv export` 永远等不到
                  # EOF 而挂起（每天第一次打开时必现）。
                  (
                    exec 0</dev/null 1>/dev/null 2>/dev/null
                    for fd in $(seq 3 255); do eval "exec $fd>&-" 2>/dev/null; done
                    exec pg_ctl -D "$PGDATA" -l "$PGLOG" start
                  )
                  sleep 1
                fi
                # 确保 blog 数据库存在
                psql -tc "SELECT 1 FROM pg_database WHERE datname = 'blog'" 2>/dev/null | grep -q 1 || createdb blog 2>/dev/null || true
                # 确保 postgres 角色存在（.env 使用 postgres 用户连接）
                psql -tc "SELECT 1 FROM pg_roles WHERE rolname = 'postgres'" 2>/dev/null | grep -q 1 || psql -c "CREATE ROLE postgres WITH LOGIN SUPERUSER;" 2>/dev/null || true
              fi

              # ── 自动运行数据库迁移 ──────────────────────────────────
              if [ -f artisan ] && [ -f .env ]; then
                php artisan migrate --force 2>/dev/null || true
              fi

              if [ ! -f .env ]; then
                echo ""
                echo "  ╔══════════════════════════════════════════════════╗"
                echo "  ║  Missing .env — run  cp .env.example .env       ║"
                echo "  ║  then  php artisan key:generate                  ║"
                echo "  ╚══════════════════════════════════════════════════╝"
                echo ""
              fi

              echo ""
              echo "  🧊  Laravel Blog devShell ($SYSTEM_LABEL)"
              echo "  ───────────────────────────────"
              echo "  PHP    $(php -r 'echo PHP_VERSION;')"
              echo "  Composer  $(composer --version 2>&1 | grep -oE '[0-9]+\.[0-9]+\.[0-9]+')"
              echo "  Node   $(node --version)"
              echo "  npm    $(npm --version)"
              echo "  psql   $(psql --version 2>/dev/null || echo '(not in PATH)')"
              echo ""
            '';
          };

          # ── frontend：只做前端构建（Node + Vite）────────────────────
          frontend = pkgs.mkShell {
            buildInputs = with pkgs; [
              nodejs
            ];

            shellHook = ''
              echo "  🎨  Frontend devShell"
              echo "  ─────────────────────"
              echo "  Node   $(node --version)"
              echo "  npm    $(npm --version)"
              echo ""
            '';
          };

          # ── backend：只做后端开发（PHP + Composer）──────────────────
          backend = pkgs.mkShell {
            buildInputs = with pkgs; [
              php composer postgresql_16
              phpactor                          # PHP LSP
            ];

            shellHook = ''
              echo "  ⚙️  Backend devShell"
              echo "  ────────────────────"
              echo "  PHP    $(php -r 'echo PHP_VERSION;')"
              echo "  Composer  $(composer --version 2>&1 | grep -oE '[0-9]+\.[0-9]+\.[0-9]+')"
              echo ""
            '';
          };

        }; # devShells

        formatter = pkgs.nixpkgs-fmt;
      }
    );
}
