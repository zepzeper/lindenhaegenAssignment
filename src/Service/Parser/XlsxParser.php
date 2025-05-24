<?php

namespace App\Service\Parser;

use DOMDocument;
use ZipArchive;
use Exception;
use App\Contracts\Parser as ParserContract;

// Could have used PhpSpreadsheet
class XlsxParser implements ParserContract
{
    public function __construct()
    {
        if (!extension_loaded('zip')) {
            throw new Exception('ZIP extension is required for XLSX parsing');
        }
    }

    public function parse(string $filePath, ?int $sheetNumber): array
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: {$filePath}");
        }

        if (is_null($sheetNumber)) {
            throw new Exception("Sheetnumber not set");
        }

        $zip = new ZipArchive();
        $result = $zip->open($filePath, ZipArchive::RDONLY);

        if ($result !== true) {
            throw new Exception("Cannot open XLSX file: {$filePath} (Error code: {$result})");
        }

        try {
            // Parse the worksheet
            $sheetPath = "xl/worksheets/sheet{$sheetNumber}.xml";
            $worksheetXml = $zip->getFromName($sheetPath);

            if ($worksheetXml === false) {
                throw new Exception("Sheet {$sheetNumber} not found in workbook");
            }

            return $this->parseWorksheet($worksheetXml);
        } finally {
            $zip->close();
        }
    }

    private function parseWorksheet(string $worksheetXml): array
    {
        $dom = new DOMDocument();
        if (!$dom->loadXML($worksheetXml)) {
            throw new Exception('Invalid worksheet XML content');
        }

        $rows = $dom->getElementsByTagName('row');
        $rawData = [];

        foreach ($rows as $row) {
            $rowNumber = (int)$row->getAttribute('r');
            $rowData = $this->processRow($row);

            if (!empty($rowData)) {
                $rawData[$rowNumber] = $rowData;
            }
        }

        return $rawData;
    }

    private function processRow($row): array
    {
        $cells = $row->getElementsByTagName('c');
        $rowData = [];

        foreach ($cells as $key => $cell) {
            $cellRef = $cell->getAttribute('r');
            $column = $this->getColumnFromCellRef($cellRef);
            $value = $this->getCellValue($cell);

            $rowData[$key] = $value;
        }

        return $rowData;
    }

    private function getCellValue($cell): string|float|bool
    {
        // Check for inline strings first
        $isNode = $cell->getElementsByTagName('is')->item(0);
        if ($isNode) {
            $tNode = $isNode->getElementsByTagName('t')->item(0);
            return $tNode ? trim($tNode->nodeValue) : '';
        }

        // Get value node
        $valueNode = $cell->getElementsByTagName('v')->item(0);
        if (!$valueNode) {
            return '';
        }

        $value = $valueNode->nodeValue;
        $cellType = $cell->getAttribute('t');

        return match ($cellType) {
            'b' => $value === '1', // Boolean
            'n', '' => is_numeric($value) ? (float)$value : $value, // Number
            default => $value
        };
    }

    private function getColumnFromCellRef(string $cellRef): string
    {
        return preg_replace('/\d+/', '', $cellRef);
    }
}
