<?php

namespace App\Contracts;

interface Parser
{
    public function parse(string $filePath, ?int $sheetNumber): array;
}
