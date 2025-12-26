<?php
function slugifyName(string $name): string
{
    $name = trim($name);
    if ($name === '') {
        return '';
    }

    $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT', $name);
    if ($transliterated === false) {
        $transliterated = $name;
    }

    $upper = strtoupper($transliterated);
    $slug = preg_replace('/[^A-Z0-9]+/i', '_', $upper);
    $slug = preg_replace('/_+/', '_', $slug ?? '');
    return trim($slug, '_');
}

$name = "ADRIAN VÍCTOR ANDRÉS YAÑEZ ROJAS";
$slug = slugifyName($name);
echo "Name: $name\n";
echo "Slug: $slug\n";
