<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ExcelExport
{
    public static function download(string $filename, array $headers, iterable $rows, string $sheetName = 'Report'): BinaryFileResponse
    {
        $safeFilename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?: 'report';
        $safeFilename = preg_replace('/\.xlsx$/i', '', $safeFilename) . '.xlsx';
        $normalizedRows = collect($rows)->map(fn($row) => array_values((array) $row))->values();
        $headers = array_values($headers);
        $columnCount = max(count($headers), 1);
        $lastColumn = self::columnName($columnCount);
        $lastRow = max($normalizedRows->count() + 1, 1);
        $widths = self::columnWidths($headers, $normalizedRows, $columnCount);
        $sheetName = str_replace(['\\', '/', '*', '?', ':', '[', ']'], '', $sheetName);
        $sheetName = mb_substr($sheetName !== '' ? $sheetName : 'Report', 0, 31);

        $temporaryPath = tempnam(sys_get_temp_dir(), 'depth-xlsx-');
        if ($temporaryPath === false) {
            throw new \RuntimeException('Unable to create the Excel export file.');
        }

        $zip = new ZipArchive();
        if ($zip->open($temporaryPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($temporaryPath);
            throw new \RuntimeException('Unable to create the Excel workbook.');
        }

        $zip->addFromString('[Content_Types].xml', self::contentTypesXml());
        $zip->addFromString('_rels/.rels', self::rootRelationshipsXml());
        $zip->addFromString('xl/workbook.xml', self::workbookXml($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', self::workbookRelationshipsXml());
        $zip->addFromString('xl/styles.xml', self::stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', self::worksheetXml($headers, $normalizedRows, $widths, $lastColumn, $lastRow));
        $zip->close();

        return response()->download($temporaryPath, $safeFilename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
        ])->deleteFileAfterSend(true);
    }

    private static function worksheetXml(array $headers, $rows, array $widths, string $lastColumn, int $lastRow): string
    {
        $columnsXml = '';
        foreach ($widths as $index => $width) {
            $columnNumber = $index + 1;
            $columnsXml .= '<col min="' . $columnNumber . '" max="' . $columnNumber . '" width="' . $width . '" customWidth="1"/>';
        }

        $sheetRows = '<row r="1">';
        foreach ($headers as $index => $header) {
            $sheetRows .= self::cellXml($index + 1, 1, $header, 1);
        }
        $sheetRows .= '</row>';

        foreach ($rows as $rowIndex => $row) {
            $excelRow = $rowIndex + 2;
            $sheetRows .= '<row r="' . $excelRow . '">';
            for ($columnIndex = 0; $columnIndex < count($headers); $columnIndex++) {
                $sheetRows .= self::cellXml($columnIndex + 1, $excelRow, $row[$columnIndex] ?? '', 2);
            }
            $sheetRows .= '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="A1:' . $lastColumn . $lastRow . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<cols>' . $columnsXml . '</cols>'
            . '<sheetData>' . $sheetRows . '</sheetData>'
            . '<autoFilter ref="A1:' . $lastColumn . $lastRow . '"/>'
            . '</worksheet>';
    }

    private static function cellXml(int $column, int $row, mixed $value, int $style): string
    {
        $reference = self::columnName($column) . $row;
        if (is_int($value) || is_float($value)) {
            return '<c r="' . $reference . '" s="' . $style . '"><v>' . $value . '</v></c>';
        }

        $text = self::xmlText($value);
        $preserve = preg_match('/^\s|\s$/u', $text) ? ' xml:space="preserve"' : '';

        return '<c r="' . $reference . '" s="' . $style . '" t="inlineStr"><is><t' . $preserve . '>'
            . htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            . '</t></is></c>';
    }

    private static function columnWidths(array $headers, $rows, int $columnCount): array
    {
        $widths = [];
        for ($column = 0; $column < $columnCount; $column++) {
            $maxLength = mb_strlen((string) ($headers[$column] ?? ''));
            foreach ($rows as $row) {
                $value = self::xmlText($row[$column] ?? '');
                foreach (preg_split('/\R/u', $value) ?: [$value] as $line) {
                    $maxLength = max($maxLength, mb_strlen($line));
                }
            }
            $widths[$column] = min(max($maxLength + 2, 10), 60);
        }

        return $widths;
    }

    private static function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)) . $name;
            $number = intdiv($number, 26);
        }
        return $name;
    }

    private static function xmlText(mixed $value): string
    {
        if ($value === null) return '';
        if (is_bool($value)) return $value ? 'Yes' : 'No';
        return preg_replace('/[^\P{C}\t\r\n]/u', '', (string) $value) ?? '';
    }

    private static function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private static function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private static function workbookXml(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . htmlspecialchars($sheetName, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private static function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private static function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="2"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment vertical="top" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"><alignment vertical="top" wrapText="1"/></xf></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}
