<?php

namespace App\Service\Factory;

use App\Service\Parser\XlsxParser;
use App\Contracts\Parser;
use Exception;

class FileParserFactory
{
    private array $parsers = [];

    public function __construct()
    {
        $this->parsers = [
            'xlsx' => XlsxParser::class
        ]; // Extend with more...
    }

    public function createParser(string $fileExtension): Parser
    {
        $extension = strtolower($fileExtension);

        if (!$this->supports($extension)) {
            throw new Exception("Unsupported file type: {$extension}");
        }

        $parserClass = $this->parsers[$extension];
        return new $parserClass();
    }

    public function supports(string $fileExtension): bool
    {
        return isset($this->parsers[strtolower($fileExtension)]);
    }
}
