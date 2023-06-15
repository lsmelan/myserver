<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetService
{
    public function getRows(string $filePath, int $chunkSize = 1000): \Generator
    {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();

        for ($row = 1; $row <= $highestRow; $row += $chunkSize) {
            $endRow = $row + $chunkSize - 1;
            if ($endRow > $highestRow) {
                $endRow = $highestRow;
            }

            $range = 'A' . $row . ':' . $highestColumn . $endRow;
            yield $worksheet->rangeToArray($range, returnCellRef: true);
        }
    }
}
