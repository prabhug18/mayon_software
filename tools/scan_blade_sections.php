<?php
$root = __DIR__ . '/../resources/views';
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($rii as $file) {
    if (!$file->isFile()) continue;
    if (substr($file->getFilename(), -10) !== '.blade.php') continue;
    $content = file_get_contents($file->getPathname());
    // Count total @section occurrences (both block and inline) vs @endsection
    $starts = preg_match_all('/@section\b/', $content);
    $ends = preg_match_all('/@endsection\b/', $content);
    if ($ends > $starts) {
        echo $file->getPathname() . " : sections=$starts ends=$ends\n";
    }
}
