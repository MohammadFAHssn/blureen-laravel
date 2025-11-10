<?php

// TODO: Relativity Pachol

namespace App\Exports\Evaluation;

use App\Models\Evaluation\Evaluatee;
use App\Models\Evaluation\EvaluationQuestionCategory;
use Maatwebsite\Excel\Concerns\FromArray;
// use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class EvaluationResultsExport implements FromArray, WithEvents
{
    protected int $month;

    protected int $year;

    protected array $data = [];

    protected array $userRowMerges = [];

    protected array $topHeaderMerges = [];

    protected int $columnCount = 0;

    protected int $categoryCount = 0;
    public function __construct(int $month, int $year)
    {
        $this->month = $month;
        $this->year = $year;

        $this->build();
    }

    public function array(): array
    {
        return $this->data;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // RTL layout for Persian
                $sheet->setRightToLeft(true);

                // Freeze header rows
                $sheet->freezePane('A3');

                // Merge top headers (row 1)
                foreach ($this->topHeaderMerges as $merge) {
                    $startCol = $this->col($merge['startCol']);
                    $endCol = $this->col($merge['endCol']);
                    $sheet->mergeCells("{$startCol}1:{$endCol}1");
                }

                // Merge final score
                $finalCol = $this->columnCount;
                $endColLetter = $this->col($this->columnCount);
                $sheet->mergeCells("{$endColLetter}1:{$endColLetter}2");


                // Merge user columns vertically per user group (rows start at 3)
                foreach ($this->userRowMerges as $index => $block) {
                    $start = $block['start'];
                    $end = $block['end'];

                    if ($end <= $start) {
                        continue;
                    }
                    // First 4 columns belong to "کاربر"
                    for ($c = 1; $c <= 4; $c++) {
                        $col = $this->col($c);
                        $sheet->mergeCells("{$col}{$start}:{$col}{$end}");
                    }

                    $avgBlockStart = $this->columnCount - ($this->categoryCount - 1) - 1;
                    for ($c = $avgBlockStart; $c <= $finalCol; $c++) { // - 1 for final score
                        $col = $this->col($c);
                        $sheet->mergeCells("{$col}{$start}:{$col}{$end}");
                    }


                    // apply alternating row fill colors for user row blocks
                    // Color every second block (index 1, 3, 5, ...)
                    if ($index % 2 === 1) {
                        $range = "A{$start}:{$endColLetter}{$end}";
                        $sheet->getStyle($range)->applyFromArray([
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'D9D9D9'],
                            ],
                        ]);
                    }
                }

                // Apply thin borders to the whole data table (headers + rows)
                $lastRow = count($this->data);
                if ($lastRow > 0 && $this->columnCount > 0) {
                    $sheet->getStyle("A1:{$endColLetter}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['argb' => 'FFBFBFBF'],
                            ],
                        ],
                    ]);
                }
            },
        ];
    }

    protected function build(): void
    {
        // 1)
        $categories = EvaluationQuestionCategory::with([
            'questions' => function ($query) {
                $query->active()->orderBy('id');
            },
        ])->get()->filter(fn($category) => $category->questions->count() > 0)->values();

        $this->categoryCount = $categories->count();

        // 2) Build header rows
        $header1 = [];
        $header2 = [];

        // User section (4 cols)
        $header1 = array_merge($header1, ['کاربر', '', '', '']);
        $header2 = array_merge($header2, ['کد پرسنلی', 'نام', 'نام خانوادگی', 'مرکز هزینه']);
        $this->topHeaderMerges[] = ['startCol' => 1, 'endCol' => 4];

        // Evaluator section (4 cols)
        $startEval = count($header1) + 1; // 5
        $header1 = array_merge($header1, ['ارزیاب', '', '', '']);
        $header2 = array_merge($header2, ['کد پرسنلی', 'نام', 'نام خانوادگی', 'مرکز هزینه']);
        $this->topHeaderMerges[] = ['startCol' => $startEval, 'endCol' => $startEval + 3];

        // Categories (variable)
        $currentCol = count($header1) + 1; // next after evaluator
        foreach ($categories as $category) {
            $questionCount = $category->questions->count();
            if ($questionCount === 0) {
                continue;
            }
            $label = $category->name;
            // First row header for this category spans questionCount columns
            $header1[] = $label;
            for ($i = 1; $i < $questionCount + 1; $i++) { // +1 for category score
                $header1[] = '';
            }
            // Second row subheaders: سوال 1..N
            for ($i = 1; $i <= $questionCount; $i++) {
                $header2[] = 'سوال ' . $i;
            }

            $header2[] = 'امتیاز ' . $label;

            $this->topHeaderMerges[] = [
                'startCol' => $currentCol,
                'endCol' => $currentCol + ($questionCount - 1) + 1, // +1 for category score
            ];
            $currentCol += $questionCount + 1; // +1 for category score
        }

        // average
        $header1[] = 'میانگین';
        foreach ($categories as $index => $category) {
            $header2[] = $category->name;

            if ($index > 0) {
                $header1[] = '';
            }
        }

        $header1[] = 'امتیاز کل';

        $this->topHeaderMerges[] = [
            'startCol' => $currentCol,
            'endCol' => $currentCol + $this->categoryCount - 1,
        ];

        $this->data[] = $header1;
        $this->data[] = $header2;

        $this->columnCount = max($this->columnCount, count($header1));


        // 3) Load evaluatees for given month/year with relations
        $evaluatees = Evaluatee::whereHas('evaluator.evaluation', function ($query) {
            $query->where('month', $this->month)->where('year', $this->year);
        })->select('id', 'user_id', 'evaluator_id')->with([
                    'user:id,personnel_code,first_name,last_name',
                    'user.profile:id,user_id,cost_center_id',
                    'user.profile.costCenter:name,rayvarz_id',
                    'evaluator.user:id,personnel_code,first_name,last_name',
                    'evaluator.user.profile:id,user_id,cost_center_id',
                    'evaluator.user.profile.costCenter:name,rayvarz_id',
                    'scores.question:id,category_id',
                ])->get();

        // Group by user to merge user columns across multiple evaluator rows
        $groups = $evaluatees->groupBy('user_id');

        $rowPointer = 3; // first data row in Excel
        foreach ($groups as $rows) {
            $groupCount = $rows->count();
            $startRow = $rowPointer;

            $averageCategoryScoreOnEvaluators = array_fill(0, $this->categoryCount, 0);
            $evaluateesThatHasScore = 0;
            foreach ($rows as $evaluatee) {
                $user = $evaluatee->user;
                $userCostCenter = $user->profile?->costCenter?->name;

                $evaluator = $evaluatee->evaluator->user;
                $evalCostCenter = $evaluator->profile?->costCenter?->name;

                $record = [
                    // User block
                    $user?->personnel_code,
                    $user?->first_name,
                    $user?->last_name,
                    $userCostCenter,

                    // Evaluator block
                    $evaluator?->personnel_code,
                    $evaluator?->first_name,
                    $evaluator?->last_name,
                    $evalCostCenter,
                ];

                // Prepare score map for quick lookup
                $scoreByQuestion = collect($evaluatee->scores)->keyBy('question_id');

                if ($scoreByQuestion->isNotEmpty()) {
                    $evaluateesThatHasScore++;
                }

                foreach ($categories as $key => $category) {
                    $totalCategoryScoreOnQuestions = 0;
                    foreach ($category->questions as $question) {
                        $score = $scoreByQuestion->get($question->id)?->score;
                        $record[] = $score * 2 ?? '';
                        $totalCategoryScoreOnQuestions += $score * 2 ?? 0;
                    }
                    $questionCount = $category->questions->count();
                    $averageCategoryScoreOnQuestions = round($totalCategoryScoreOnQuestions / $questionCount, 2);
                    $record[] = $averageCategoryScoreOnQuestions;

                    $averageCategoryScoreOnEvaluators[$key] += $averageCategoryScoreOnQuestions;
                }

                foreach ($categories as $key => $category) {
                    // Placeholder for average category scores on evaluators
                    $record[] = '';
                }

                $record[] = ''; // Placeholder for final score

                $this->data[] = $record;
                $rowPointer++;
            }

            foreach ($categories as $key => $category) {
                if ($evaluateesThatHasScore > 0) {
                    $averageCategoryScoreOnEvaluators[$key] = round($averageCategoryScoreOnEvaluators[$key] / $evaluateesThatHasScore, 2);
                } else {
                    $averageCategoryScoreOnEvaluators[$key] = 0;
                }
                $this->data[$startRow - 1][$this->columnCount - ($this->categoryCount + 1) + $key] = $averageCategoryScoreOnEvaluators[$key];
            }

            $finalScore = round(array_sum($averageCategoryScoreOnEvaluators)
                / count($averageCategoryScoreOnEvaluators), 2);

            $this->data[$startRow - 1][$this->columnCount - 1] = $finalScore;

            // register merge block for user columns if more than one evaluator row
            if ($groupCount > 1) {
                $this->userRowMerges[] = ['start' => $startRow, 'end' => $rowPointer - 1];
            }
        }
    }

    /**
     * Convert 1-based column index to Excel column letters (A, B, ..., AA, AB, ...)
     */
    protected function col(int $index): string
    {
        $index = max(1, $index);
        $letters = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letters = chr(65 + $mod) . $letters;
            $index = intdiv($index - 1, 26);
        }
        return $letters;
    }
}
