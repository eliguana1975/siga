<?php

namespace App\Support;

class Code39Barcode
{
    private const PATTERNS = [
        '0' => 'nnnwwnwnn',
        '1' => 'wnnwnnnnw',
        '2' => 'nnwwnnnnw',
        '3' => 'wnwwnnnnn',
        '4' => 'nnnwwnnnw',
        '5' => 'wnnwwnnnn',
        '6' => 'nnwwwnnnn',
        '7' => 'nnnwnnwnw',
        '8' => 'wnnwnnwnn',
        '9' => 'nnwwnnwnn',
        'A' => 'wnnnnwnnw',
        'B' => 'nnwnnwnnw',
        'C' => 'wnwnnwnnn',
        'D' => 'nnnnwwnnw',
        'E' => 'wnnnwwnnn',
        'F' => 'nnwnwwnnn',
        'G' => 'nnnnnwwnw',
        'H' => 'wnnnnwwnn',
        'I' => 'nnwnnwwnn',
        'J' => 'nnnnwwwnn',
        'K' => 'wnnnnnnww',
        'L' => 'nnwnnnnww',
        'M' => 'wnwnnnnwn',
        'N' => 'nnnnwnnww',
        'O' => 'wnnnwnnwn',
        'P' => 'nnwnwnnwn',
        'Q' => 'nnnnnnwww',
        'R' => 'wnnnnnwwn',
        'S' => 'nnwnnnwwn',
        'T' => 'nnnnwnwwn',
        'U' => 'wwnnnnnnw',
        'V' => 'nwwnnnnnw',
        'W' => 'wwwnnnnnn',
        'X' => 'nwnnwnnnw',
        'Y' => 'wwnnwnnnn',
        'Z' => 'nwwnwnnnn',
        '-' => 'nwnnnnwnw',
        '.' => 'wwnnnnwnn',
        ' ' => 'nwwnnnwnn',
        '$' => 'nwnwnwnnn',
        '/' => 'nwnwnnnwn',
        '+' => 'nwnnnwnwn',
        '%' => 'nnnwnwnwn',
        '*' => 'nwnnwnwnn',
    ];

    public static function svgDataUri(string $code): string
    {
        $code = self::normalize($code);
        $encoded = '*' . $code . '*';
        $narrow = 2;
        $wide = 5;
        $height = 62;
        $margin = 8;
        $x = $margin;
        $rects = [];

        foreach (str_split($encoded) as $char) {
            $pattern = self::PATTERNS[$char] ?? self::PATTERNS['-'];

            foreach (str_split($pattern) as $index => $widthCode) {
                $width = $widthCode === 'w' ? $wide : $narrow;

                if ($index % 2 === 0) {
                    $rects[] = '<rect x="' . $x . '" y="' . $margin . '" width="' . $width . '" height="' . $height . '" fill="#111827"/>';
                }

                $x += $width;
            }

            $x += $narrow;
        }

        $width = $x + $margin;
        $labelY = $height + ($margin * 2) + 12;
        $svgHeight = $labelY + 10;
        $label = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $width . '" height="' . $svgHeight . '" viewBox="0 0 ' . $width . ' ' . $svgHeight . '">'
            . '<rect width="100%" height="100%" fill="#ffffff"/>'
            . implode('', $rects)
            . '<text x="' . ($width / 2) . '" y="' . $labelY . '" text-anchor="middle" font-family="Arial, sans-serif" font-size="12" font-weight="700" fill="#111827">' . $label . '</text>'
            . '</svg>';

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    private static function normalize(string $code): string
    {
        $code = strtoupper(trim($code));
        $code = preg_replace('/[^A-Z0-9\-\.\ \$\/\+\%]/', '-', $code) ?: 'SIN-CODIGO';

        return $code;
    }
}
