<?php

namespace App\Service;

class Calculate
{
    private const MIN_GRADE = 1.0;
    private const MAX_GRADE = 10.0;
    private const PASS_THRESHOLD = 70.0;
    private const PASS_GRADE = 5.5;
    private const MIN_THRESHOLD = 20.0;

    public function calculateStudentScores(array $studentScores, int $maxScore): array
    {
        $studentResults = [];

        foreach ($studentScores as $studentId => $scores) {
            $totalScore = array_sum($scores);
            $grade = $this->calculateGrade($totalScore, $maxScore);
            $percentage = ($totalScore / $maxScore) * 100;

            $studentResults[$studentId] = [
                'totalScore' => $totalScore,
                'maxScore' => $maxScore,
                'percentage' => round($percentage, 1),
                'grade' => $grade,
                'passed' => $grade >= 5.5
            ];
        }

        return $studentResults;
    }

    private function calculateGrade(float $score, float $maxScore): float
    {
        if ($maxScore <= 0) {
            throw new \InvalidArgumentException("Maximum score must be greater than 0");
        }

        if ($score < 0) {
            $score = 0;
        }

        $percentage = ($score / $maxScore) * 100;

        return $this->percentageToGrade($percentage);
    }

    private function percentageToGrade(float $percentage): float
    {
        if ($percentage <= self::MIN_THRESHOLD) {
            $grade = self::MIN_GRADE; // At least 20 points otherwise 1.0
        } elseif ($percentage < self::PASS_THRESHOLD) {
            $grade = self::MIN_GRADE +
                     (($percentage - self::MIN_THRESHOLD) / (self::PASS_THRESHOLD - self::MIN_THRESHOLD)) *
                     (self::PASS_GRADE - self::MIN_GRADE); // At least 70 points
        } else {
            $grade = self::PASS_GRADE +
                     (($percentage - self::PASS_THRESHOLD) / (100 - self::PASS_THRESHOLD)) *
                     (self::MAX_GRADE - self::PASS_GRADE);
        }

        return round($grade, 1);
    }
}
