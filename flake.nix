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
