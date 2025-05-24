<?php

namespace App\Command;

use App\Service\Calculate;
use App\Service\Factory\FileParserFactory;
use App\Service\StudentScores;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Helper\QuestionHelper;

#[AsCommand(
    name: 'exam:analyze',
)]
class Analyzer extends Command
{
    private Calculate $calculator;

    private FileParserFactory $parserFactory;

    private StudentScores $formatter;

    public function __construct()
    {
        parent::__construct();
        $this->parserFactory = new FileParserFactory();
        $this->formatter = new StudentScores();
        $this->calculator = new Calculate();
    }

    protected function configure(): void
    {
        $this->addArgument('file-path', InputArgument::REQUIRED, 'Path to the Excel file with exam results');
        $this->addArgument('sheet-number', InputArgument::REQUIRED, 'Index of the sheet number');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get args
        $filePath = $input->getArgument('file-path');
        $sheetNumber = (int) $input->getArgument('sheet-number');

        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if (!$this->parserFactory->supports($fileExtension)) {
            $output->writeln("<error>Unsupported file type: {$fileExtension}</error>");
            return Command::FAILURE;
        }

        $parser = $this->parserFactory->createParser($fileExtension);

        $rawResults = $parser->parse($filePath, $sheetNumber);

        $this->formatter->format($rawResults);

        $studentResults = $this->calculator->calculateStudentScores($this->formatter->getStudentScores(), array_sum($this->formatter->getMaxScores()));

        $this->displayResults($output, $studentResults);
        $this->displaySummary($output, $studentResults);

        $helper = new QuestionHelper();

        while (true) {
            $output->writeln('');
            $output->writeln('<info>╔═══════════════════════════════════════╗</info>');
            $output->writeln('<info>║           Available Options          ║</info>');
            $output->writeln('<info>╠═══════════════════════════════════════╣</info>');
            $output->writeln('<info>║</info> <comment>1.</comment> Calculate P value                <info>║</info>');
            $output->writeln('<info>║</info> <comment>2.</comment> Calculate R value                <info>║</info>');
            $output->writeln('<info>║</info> <comment>3.</comment> Exit                             <info>║</info>');
            $output->writeln('<info>╚═══════════════════════════════════════╝</info>');
            $output->writeln('');

            $question = new Question('<question>Select an option (1-3): </question>');
            $choice = $helper->ask($input, $output, $question);

            switch ($choice) {
                case '1':
                    $this->handlePValueCalculation($input, $output, $helper, $studentResults);
                    break;
                case '2':
                    $this->handleRValueCalculation($input, $output, $helper, $studentResults);
                    break;
                case '3':
                    $output->writeln('Goodbye!');
                    return Command::SUCCESS;
                default:
                    $output->writeln('<error>Invalid option. Please select 1, 2, or 3.</error>');
                    break;
            }
        }

        return Command::SUCCESS;
    }

    private function displayResults(OutputInterface $output, array $studentResults): void
    {
        $table = new Table($output);
        $table->setHeaders([
            'Student ID', 'Total Score', 'Max Score', 'Percentage', 'Grade', 'Result'
        ]);

        foreach ($studentResults as $studentId => $result) {
            $resultStatus = $result['passed'] ? '<fg=green>PASSED</>' : '<fg=red>FAILED</>';
            $table->addRow([
                $studentId,
                $result['totalScore'],
                $result['maxScore'],
                $result['percentage'] . '%',
                $result['grade'],
                $resultStatus
            ]);
        }

        $table->render();
    }

    private function displaySummary(OutputInterface $output, array $studentResults): void
    {
        $totalStudents = count($studentResults);
        $passedStudents = count(array_filter($studentResults, fn($r) => $r['passed']));
        $failedStudents = $totalStudents - $passedStudents;
        $passRate = $totalStudents > 0 ? round(($passedStudents / $totalStudents) * 100, 1) : 0;
        $averageGrade = $totalStudents > 0 ? round(array_sum(array_column($studentResults, 'grade')) / $totalStudents, 1) : 0;

        $output->writeln('');
        $output->writeln('<comment>Summary Statistics:</comment>');
        $output->writeln("Total Students: {$totalStudents}");
        $output->writeln("Passed: <fg=green>{$passedStudents}</>");
        $output->writeln("Failed: <fg=red>{$failedStudents}</>");
        $output->writeln("Pass Rate: {$passRate}%");
        $output->writeln("Average Grade: {$averageGrade}");
    }

    private function handlePValueCalculation(InputInterface $input, OutputInterface $output, QuestionHelper $helper)
    {
        $output->writeln('');
        $output->writeln('<info>P Value Calculation</info>');
        $output->writeln('<comment>═══════════════════════</comment>');
        $question = new Question('Enter the question number for P value calculation: ');
        $index = $helper->ask($input, $output, $question);

        $maxQuestions = count($this->formatter->getMaxScores());
        if ($index > $maxQuestions || $index < 1) {
            $output->writeln("<error>Invalid question number. There are only {$maxQuestions} questions available (1-{$maxQuestions}).</error>");
            return $this->handlePValueCalculation($input, $output, $helper);
        }

        // Add your P value calculation logic here
        $output->writeln("Calculating P value for student at index: {$index}");
        $output->writeln("P Value: {$this->formatter->getPValue($index)}");

    }

    private function handleRValueCalculation(InputInterface $input, OutputInterface $output, QuestionHelper $helper)
    {
        $output->writeln('');
        $output->writeln('<info>R Value Calculation</info>');
        $output->writeln('<comment>═══════════════════════</comment>');
        $output->writeln("Not Implemented... :(");

    }
}
