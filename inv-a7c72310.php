<?php
// Inventaire temporaire (lecture seule) du dossier www de l'hebergement : tailles et noms, aucun contenu de fichier.
// Protege par jeton (seule son empreinte figure ici). A supprimer apres usage.
if (!isset($_GET['k']) || !hash_equals('3e991abefbf945397befb0f01d3c41eaee6565a0d3ef643b13243cd1c048b2c4', hash('sha256', (string)$_GET['k']))) { http_response_code(404); exit; }
@set_time_limit(300); header('Content-Type: application/json; charset=utf-8');
$home = dirname(__DIR__); $www = $home . '/www';
$tree = array(); $exts = array(); $big = array(); $n = 0; $total = 0; $years = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($www, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);
foreach ($it as $f) {
  if (!$f->isFile()) continue;
  $s = $f->getSize(); $n++; $total += $s;
  $rel = substr($f->getPathname(), strlen($www) + 1); $parts = explode('/', $rel);
  for ($d = 1; $d <= min(4, count($parts) - 1); $d++) { $k = implode('/', array_slice($parts, 0, $d)); if (!isset($tree[$k])) $tree[$k] = array(0, 0); $tree[$k][0] += $s; $tree[$k][1]++; }
  $e = strtolower(pathinfo($rel, PATHINFO_EXTENSION)); if (!isset($exts[$e])) $exts[$e] = array(0, 0); $exts[$e][0] += $s; $exts[$e][1]++;
  $y = date('Y', $f->getMTime()); if (!isset($years[$y])) $years[$y] = array(0, 0); $years[$y][0] += $s; $years[$y][1]++;
  if ($s > 20000000) $big[] = array($rel, $s, date('Y-m-d', $f->getMTime()));
}
$root = array();
foreach (scandir($home) as $x) { if ($x === '.' || $x === '..') continue; $root[] = array($x, is_dir("$home/$x") ? 'dir' : 'file'); }
$top = array(); foreach (scandir($www) as $x) { if ($x === '.' || $x === '..') continue; $top[] = array($x, is_dir("$www/$x") ? 'dir' : filesize("$www/$x")); }
$ver = ''; if (is_file("$www/wp-includes/version.php")) { if (preg_match("/wp_version = '([^']+)'/", file_get_contents("$www/wp-includes/version.php"), $m)) $ver = $m[1]; }
uasort($tree, function ($a, $b) { return $b[0] - $a[0]; }); arsort($exts); usort($big, function ($a, $b) { return $b[1] - $a[1]; }); ksort($years);
echo json_encode(array('total_octets' => $total, 'fichiers' => $n, 'wp_version' => $ver, 'racine_hebergement' => $root, 'www_niveau1' => $top,
  'dossiers' => array_slice($tree, 0, 150, true), 'extensions' => array_slice($exts, 0, 25, true), 'par_annee_modif' => $years, 'gros_fichiers' => array_slice($big, 0, 60)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
