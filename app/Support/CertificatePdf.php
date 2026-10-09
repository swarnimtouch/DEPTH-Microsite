<?php

namespace App\Support;

class CertificatePdf
{
    public static function delegateCertificate(
        string $templatePath,
        string $delegateName,
        ?float $nameX = null,
        ?float $nameY = null,
        ?int $fontSize = null,
        ?string $fontPath = null
    ): string
    {
        $pdf = file_get_contents($templatePath);
        $delegateName = self::cleanText($delegateName);
        $fontPath = $fontPath && is_file($fontPath) && self::isEmbeddableTrueType($fontPath) ? $fontPath : null;

        $objectMap = self::objects($pdf);
        $maxObjectId = max(array_keys($objectMap));
        $fontFileObjectId = $fontPath ? $maxObjectId + 1 : null;
        $fontDescriptorObjectId = $fontPath ? $maxObjectId + 2 : null;
        $fontObjectId = $fontPath ? $maxObjectId + 3 : $maxObjectId + 1;
        $contentObjectId = $fontObjectId + 1;

        $pageObjectId = self::firstPageObjectId($objectMap);
        if (!$pageObjectId) {
            throw new \RuntimeException('Certificate template page was not found.');
        }

        $pageObject = $objectMap[$pageObjectId];
        $pageObject = self::addContentReference($pageObject, $contentObjectId);
        $pageObject = self::addFontResource($pageObject, $fontObjectId);

        $fontSize = $fontSize ?: self::fontSize($delegateName);
        $textWidth = strlen($delegateName) * $fontSize * 0.56;
        [$pageWidth, $pageHeight] = self::pageSize($pageObject);
        $x = $nameX ?? max(120, ($pageWidth - $textWidth) / 2);
        $y = $nameY ?? ($pageHeight * 0.49);

        $stream = implode("\n", [
            'BT',
            '/F99 ' . $fontSize . ' Tf',
            '0.557 0.373 0.086 rg',
            '1 0 0 1 ' . self::num($x) . ' ' . self::num($y) . ' Tm',
            '(' . self::escapePdfString($delegateName) . ') Tj',
            'ET',
        ]);

        $append = "\n";
        $offsets = [];

        if ($fontPath && $fontFileObjectId && $fontDescriptorObjectId) {
            $fontContents = file_get_contents($fontPath);
            $compressedFont = gzcompress($fontContents);
            if ($fontContents === false || $compressedFont === false) {
                $fontPath = null;
            }
        }

        if ($fontPath && $fontFileObjectId && $fontDescriptorObjectId) {
            $offsets[$fontFileObjectId] = strlen($pdf . $append);
            $append .= $fontFileObjectId . " 0 obj\n<</Length " . strlen($compressedFont) . "/Length1 " . strlen($fontContents) . "/Filter/FlateDecode/Subtype/OpenType>>\nstream\n" . $compressedFont . "\nendstream\nendobj\n";

            $offsets[$fontDescriptorObjectId] = strlen($pdf . $append);
            $append .= $fontDescriptorObjectId . " 0 obj\n<</Type/FontDescriptor/FontName/AbrilDisplayItalic/Flags 64/FontBBox[-300 -300 1200 1000]/ItalicAngle -12/Ascent 900/Descent -250/CapHeight 700/StemV 80/FontFile3 {$fontFileObjectId} 0 R>>\nendobj\n";

            $offsets[$fontObjectId] = strlen($pdf . $append);
            $append .= $fontObjectId . " 0 obj\n<</Type/Font/Subtype/TrueType/BaseFont/AbrilDisplayItalic/Encoding/WinAnsiEncoding/FirstChar 32/LastChar 126/Widths[" . self::fontWidths() . "]/FontDescriptor {$fontDescriptorObjectId} 0 R>>\nendobj\n";
        } else {
            $offsets[$fontObjectId] = strlen($pdf . $append);
            $append .= $fontObjectId . " 0 obj\n<</Type/Font/Subtype/Type1/BaseFont/Times-Italic/Encoding/WinAnsiEncoding>>\nendobj\n";
        }

        $offsetContent = strlen($pdf . $append);
        $append .= $contentObjectId . " 0 obj\n<</Length " . strlen($stream) . ">>\nstream\n" . $stream . "\nendstream\nendobj\n";

        $offsetPage = strlen($pdf . $append);
        $append .= $pageObjectId . " 0 obj\n" . trim($pageObject) . "\nendobj\n";

        $xrefOffset = strlen($pdf . $append);
        $previousXref = self::previousXrefOffset($pdf);
        $rootObjectId = self::catalogObjectId($objectMap);
        $infoObjectId = self::infoObjectId($pdf);
        $size = $contentObjectId + 1;

        $append .= "xref\n";
        $append .= $pageObjectId . " 1\n" . self::xrefLine($offsetPage);
        if ($fontPath && $fontFileObjectId) {
            $append .= $fontFileObjectId . " 4\n";
            for ($objectId = $fontFileObjectId; $objectId <= $contentObjectId; $objectId++) {
                $append .= self::xrefLine($objectId === $contentObjectId ? $offsetContent : $offsets[$objectId]);
            }
        } else {
            $append .= $fontObjectId . " 2\n" . self::xrefLine($offsets[$fontObjectId]) . self::xrefLine($offsetContent);
        }
        $append .= "trailer\n<</Size {$size}/Root {$rootObjectId} 0 R";
        if ($infoObjectId) {
            $append .= "/Info {$infoObjectId} 0 R";
        }
        if ($previousXref > 0) {
            $append .= "/Prev {$previousXref}";
        }
        $append .= ">>\n";
        $append .= "startxref\n{$xrefOffset}\n%%EOF\n";

        return $pdf . $append;
    }

    private static function objects(string $pdf): array
    {
        // Matching an entire object with a regex can hit PCRE's backtracking
        // limit when a certificate contains a large embedded image.
        preg_match_all('/(?:^|[\r\n])(\d+)[ \t]+0[ \t]+obj\b/', $pdf, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
        $objects = [];

        foreach ($matches as $match) {
            $start = $match[0][1] + strlen($match[0][0]);
            $end = strpos($pdf, 'endobj', $start);
            if ($end !== false) {
                $objects[(int) $match[1][0]] = substr($pdf, $start, $end - $start);
            }
        }

        // Many PDF editors put page dictionaries in compressed object streams.
        // Those objects have no `obj ... endobj` marker in the original file.
        foreach ($objects as $object) {
            if (!preg_match('/\/Type\s*\/ObjStm\b/', $object)) {
                continue;
            }

            if (!preg_match('/\/N\s+(\d+)\b/', $object, $count)
                || !preg_match('/\/First\s+(\d+)\b/', $object, $first)
                || !preg_match('/\bstream\r?\n(.*?)\r?\nendstream/s', $object, $stream)) {
                throw new \RuntimeException('Certificate template contains an invalid PDF object stream.');
            }

            $contents = $stream[1];
            if (preg_match('/\/Filter\s*(?:\[\s*)?\/FlateDecode\b/', $object)) {
                $contents = gzuncompress($contents);
                if ($contents === false) {
                    throw new \RuntimeException('Certificate template object stream could not be decoded.');
                }
            } elseif (preg_match('/\/Filter\b/', $object)) {
                throw new \RuntimeException('Certificate template uses an unsupported PDF object stream filter.');
            }

            $header = substr($contents, 0, (int) $first[1]);
            preg_match_all('/\d+/', $header, $numbers);
            if (count($numbers[0]) < (int) $count[1] * 2) {
                throw new \RuntimeException('Certificate template contains an invalid PDF object stream index.');
            }

            for ($i = 0; $i < (int) $count[1]; $i++) {
                $id = (int) $numbers[0][$i * 2];
                $start = (int) $first[1] + (int) $numbers[0][$i * 2 + 1];
                $end = $i + 1 < (int) $count[1]
                    ? (int) $first[1] + (int) $numbers[0][($i + 1) * 2 + 1]
                    : strlen($contents);
                // A later uncompressed definition takes precedence in an incremental PDF.
                $objects[$id] ??= substr($contents, $start, $end - $start);
            }
        }

        if (!$objects) {
            throw new \RuntimeException('Certificate template has no readable PDF objects.');
        }

        return $objects;
    }

    private static function catalogObjectId(array $objects): int
    {
        foreach ($objects as $id => $object) {
            if (preg_match('/\/Type\s*\/Catalog\b/', $object)) {
                return (int) $id;
            }
        }

        throw new \RuntimeException('Certificate template catalog was not found.');
    }

    private static function firstPageObjectId(array $objects): ?int
    {
        foreach ($objects as $id => $object) {
            if (preg_match('/\/Type\s*\/Page\b/', $object)) {
                return (int) $id;
            }
        }

        return null;
    }

    private static function addContentReference(string $pageObject, int $contentObjectId): string
    {
        if (preg_match('/\/Contents\s*\[([^\]]*)]/s', $pageObject)) {
            return preg_replace(
                '/\/Contents\s*\[([^\]]*)]/s',
                '/Contents[$1 ' . $contentObjectId . ' 0 R]',
                $pageObject,
                1
            );
        }

        if (preg_match('/\/Contents\s+(\d+\s+\d+\s+R)/', $pageObject)) {
            return preg_replace(
                '/\/Contents\s+(\d+\s+\d+\s+R)/',
                '/Contents[$1 ' . $contentObjectId . ' 0 R]',
                $pageObject,
                1
            );
        }

        return self::appendToDictionary($pageObject, '/Contents ' . $contentObjectId . ' 0 R');
    }

    private static function addFontResource(string $pageObject, int $fontObjectId): string
    {
        $range = self::dictionaryRangeAfter($pageObject, '/Resources');
        if (!$range) {
            return self::appendToDictionary($pageObject, '/Resources<</Font<</F99 ' . $fontObjectId . ' 0 R>>>>');
        }

        [$start, $end] = $range;
        $resources = substr($pageObject, $start, $end - $start);
        $fontRange = self::dictionaryRangeAfter($resources, '/Font');

        if ($fontRange) {
            [$fontStart, $fontEnd] = $fontRange;
            $fontDictionary = substr($resources, $fontStart, $fontEnd - $fontStart);
            $fontDictionary = self::appendToDictionary($fontDictionary, '/F99 ' . $fontObjectId . ' 0 R');
            $resources = substr_replace($resources, $fontDictionary, $fontStart, $fontEnd - $fontStart);
        } else {
            $resources = self::appendToDictionary($resources, '/Font<</F99 ' . $fontObjectId . ' 0 R>>');
        }

        return substr_replace($pageObject, $resources, $start, $end - $start);
    }

    private static function dictionaryRangeAfter(string $text, string $keyword): ?array
    {
        $keywordPos = strpos($text, $keyword);
        if ($keywordPos === false) {
            return null;
        }

        $start = strpos($text, '<<', $keywordPos);
        if ($start === false) {
            return null;
        }

        $depth = 0;
        $length = strlen($text);
        for ($i = $start; $i < $length - 1; $i++) {
            $pair = $text[$i] . $text[$i + 1];
            if ($pair === '<<') {
                $depth++;
                $i++;
                continue;
            }

            if ($pair === '>>') {
                $depth--;
                $i++;
                if ($depth === 0) {
                    return [$start, $i + 1];
                }
            }
        }

        return null;
    }

    private static function appendToDictionary(string $dictionary, string $entry): string
    {
        $lastClose = strrpos($dictionary, '>>');
        if ($lastClose === false) {
            throw new \RuntimeException('Certificate template contains an invalid PDF dictionary.');
        }

        return substr($dictionary, 0, $lastClose) . $entry . substr($dictionary, $lastClose);
    }

    private static function pageSize(string $pageObject): array
    {
        if (preg_match('/\/MediaBox\s*\[\s*[-\d.]+\s+[-\d.]+\s+([-\d.]+)\s+([-\d.]+)/', $pageObject, $match)) {
            return [(float) $match[1], (float) $match[2]];
        }

        return [841.89, 595.276];
    }

    private static function cleanText(string $text): string
    {
        $text = trim($text);
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);

        return preg_replace('/[^\x20-\x7E]/', '', $converted ?: $text) ?: 'Delegate';
    }

    private static function isEmbeddableTrueType(string $fontPath): bool
    {
        $handle = fopen($fontPath, 'rb');
        if (!$handle) {
            return false;
        }

        $signature = fread($handle, 4);
        fclose($handle);

        return $signature === "\x00\x01\x00\x00" || $signature === 'true';
    }

    private static function fontSize(string $text): int
    {
        return match (true) {
            strlen($text) > 36 => 22,
            strlen($text) > 28 => 25,
            default => 30,
        };
    }

    private static function fontWidths(): string
    {
        return implode(' ', array_fill(0, 95, 560));
    }

    private static function escapePdfString(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }

    private static function num(float $value): string
    {
        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }

    private static function xrefLine(int $offset): string
    {
        return sprintf("%010d 00000 n \n", $offset);
    }

    private static function previousXrefOffset(string $pdf): int
    {
        if (preg_match_all('/startxref\s+(\d+)/', $pdf, $matches) && $matches[1]) {
            return (int) end($matches[1]);
        }

        return 0;
    }

    private static function infoObjectId(string $pdf): ?int
    {
        if (preg_match('/\/Info\s+(\d+)\s+0\s+R/', $pdf, $match)) {
            return (int) $match[1];
        }

        return null;
    }
}
