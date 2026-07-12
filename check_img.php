<?php
require '/Volumes/T7/Project/blog/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$n = \App\Models\Note::where('title','like','%Odit eos%')->first();
if (!$n) { echo "NOT_FOUND\n"; exit; }

echo "ID=" . $n->id . "\n";
echo "CONTENT_500:\n" . substr($n->content, 0, 500) . "\n\n";

echo "=== REGEX MD IMG ===\n";
$ok = preg_match('/!\[.*?\]\(([^)]+)\)/', $n->content, $m);
echo "MATCHED: " . ($ok ? 'YES' : 'NO') . "\n";
if ($ok) var_dump($m);

echo "\n=== HTML IMG ===\n";
$h2 = preg_match('/<img[^>]+src=[\'"]([^\'"]+)/i', $n->content, $h);
echo "HTML: " . ($h2 ? $h[1] : 'NO') . "\n";

echo "\n=== RAW FIRST 800 ===\n";
echo substr($n->content, 0, 800) . "\n";
