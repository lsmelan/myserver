<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetService
{
    public const FIRST_CELL = 'A1';

    public function getRange(string $filePath, string $lastColumn = null): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $highestColumn = $lastColumn ?: $worksheet->getHighestColumn();

        $range = self::FIRST_CELL.':'.$highestColumn.$worksheet->getHighestRow();

        return $worksheet->rangeToArray($range, returnCellRef: true);
    }
}
