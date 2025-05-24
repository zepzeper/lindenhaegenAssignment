# Exam Analyzer

A Symfony console command for analyzing exam results from Excel files. This tool processes student exam data, calculates scores, grades, and provides statistical analysis with interactive P-value calculations.

## Installation

1. Ensure you have PHP 8+ and Symfony installed
2. Ensure Zip extension is loaded

## Usage

### Basic Command

```bash
php bin/console exam:analyze <file-path> <sheet-number>
```

### Parameters

- `file-path`: Path to your Excel file containing exam results
- `sheet-number`: Index of the sheet to analyze (typically 1 for first sheet)

### Example

```bash
php bin/console exam:analyze /path/to/exam_results.xlsx 1
```

## Interactive Features

After the initial analysis, you'll see an interactive menu:

```
╔═══════════════════════════════════════╗
║           Available Options          ║
╠═══════════════════════════════════════╣
║ 1. Calculate P value                 ║
║ 2. Calculate R value                 ║
║ 3. Exit                              ║
╚═══════════════════════════════════════╝
```

### Options

1. **P Value Calculation**: Calculate P-values for specific questions
2. **R Value Calculation**: *(not implemented)*
3. **Exit**: Close the application

## Output Format

### Student Results Table
- Student ID
- Total Score
- Maximum Possible Score
- Percentage
- Grade
- Pass/Fail Status (color-coded)

### Summary Statistics
- Total number of students
- Number of passed/failed students
- Pass rate percentage
- Average grade

## File Format Requirements

Your Excel file should contain:
- Student IDs in the first column
- Question scores in subsequent columns
- Maximum scores should be detectable by the parser
