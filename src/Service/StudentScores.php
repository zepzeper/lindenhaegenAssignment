<?php

namespace App\Service;

class StudentScores
{
    private array $maxScores = [];
    private array $studentScores = [];

    public function format(array $rawData): void
    {
        $this->processRawData($rawData);
    }

    private function processRawData(array $rawData): void
    {
        $studentId = 1;

        foreach ($rawData as $rowNumber => $rowData) {
            // skip header row
            if ($rowNumber === 1) {
                continue;
            }

            // max scores row
            if ($rowNumber === 2) {
                $this->extractMaxScores($rowData);
                continue;
            }

            // student data rows
            if ($rowNumber > 2) {
                $formattedRowData = $this->processStudentRow($rowData);
                if (!empty($formattedRowData)) {
                    $this->studentScores[$studentId] = $formattedRowData;
                    $studentId++;
                }
            }
        }
    }

    private function extractMaxScores(array $rowData): void
    {
        foreach ($rowData as $index => $value) {
            if ($index === 0) {
                continue;
            }

            if (is_numeric($value)) {
                $this->maxScores[$index] = (float)$value;
            } else {
                $this->maxScores[$index] = $value;
            }
        }
    }

    private function processStudentRow(array $rowData): array
    {
        $formattedData = [];

        foreach ($rowData as $index => $value) {
            if ($index === 0) {
                continue;
            }

            $formattedData[$index] = $value;
        }

        return $formattedData;
    }

    private function getAverageScoreForCol(int $index): float
    {
        $maxScore = 0;

        foreach ($this->studentScores as $scores) {
            $maxScore += $scores[$index];
        }

        return round($maxScore / count($this->studentScores), 1);
    }

    public function getPValue(int $index)
    {
        return round($this->getAverageScoreForCol($index) / $this->maxScores[$index], 2);
    }

    public function getMaxScores(): array
    {
        return $this->maxScores;
    }

    public function getStudentScores(): array
    {
        return $this->studentScores;
    }
}
