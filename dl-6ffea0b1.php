<?php
// Export temporaire du dossier www de l'hebergement (sauvegarde avant nettoyage). Protege par jeton. A supprimer apres usage.
if (!isset($_GET['k']) || !hash_equals('72cd8e951489d124cced591b5c4b70f32a3dfd027fa32610e77e9a5ec4d4a6e9', hash('sha256', (string)$_GET['k']))) { http_response_code(404); exit; }
@set_time_limit(0);
$base = realpath(dirname(__DIR__) . '/www');
if (isset($_GET['a']) && $_GET['a'] === 'list') {
  header('Content-Type: text/plain; charset=utf-8');
  $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY, RecursiveIteratorIterator::CATCH_GET_CHILD);
  foreach ($it as $f) { if ($f->isFile()) echo $f->getSize(), "\t", substr($f->getPathname(), strlen($base) + 1), "\n"; }
  exit;
}
$rel = isset($_GET['f']) ? (string)$_GET['f'] : '';
$p = realpath($base . '/' . $rel);
if ($p === false || strpos($p, $base . '/') !== 0 || !is_file($p)) { http_response_code(404); exit; }
header('Content-Type: application/octet-stream'); header('Content-Length: ' . filesize($p));
while (ob_get_level()) ob_end_clean();
readfile($p);
