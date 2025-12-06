<?php

namespace App\Exports\Evaluation;

use App\Models\Evaluation\Evaluatee;
use App\Models\Evaluation\EvaluationQuestionCategory;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Events\AfterSheet;

class EvaluationResultsExport implements FromArray, WithEvents, ShouldAutoSize
{
    /** @var int */
    protected int $month;

    /** @var int */
    protected int $year;

    /**
     * Row-based data including 2 header rows then records
     * @var array<int, array<int, string|int|null>>
     */
    protected array $data = [];

    /** @var array<int, array{start:int,end:int}> */
    protected array $userRowMerges = [];

    /** @var array<int, array{startCol:int,endCol:int,label:string}> */
    protected array $topHeaderMerges = [];

    public function __construct(int $month = 8, int $year = 1404)
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
                $event->sheet->freezePane('A3');

                // Merge top headers (row 1)
                foreach ($this->topHeaderMerges as $merge) {
                    $startCol = $this->col($merge['startCol']);
                    $endCol = $this->col($merge['endCol']);
                    $event->sheet->mergeCells("{$startCol}1:{$endCol}1");
                }

                // Merge user columns vertically per user group (rows start at 3)
                foreach ($this->userRowMerges as $block) {
                    if ($block['end'] <= $block['start']) {
                        continue;
                    }
                    // First 4 columns belong to "کاربر"
                    for ($c = 1; $c <= 4; $c++) {
                        $col = $this->col($c);
                        $event->sheet->mergeCells("{$col}{$block['start']}:{$col}{$block['end']}");
                    }
                }

                // Style headers
                $lastCol = $this->col(count($this->data[0] ?? []));
                // Row 1 style
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                ]);
                // Row 2 style
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E7F1FF']],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                ]);

                // General cells style for data area (from row 3)
                $lastRow = 2 + max(1, count($this->data) - 2);
                $sheet->getStyle("A3:{$lastCol}{$lastRow}")->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
                ]);
                // Slightly different background for the user columns to mirror the mockup
                $sheet->getStyle("A1:D{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EDEDED');
                $sheet->getStyle("E1:H{$lastRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('DDEBFF');
            },
        ];
    }

    /**
     * Build $this->data and merge instructions
     */
    protected function build(): void
    {
        // 1) Load categories with active questions
        $categories = EvaluationQuestionCategory::with([
            'questions' => function ($q) {
                $q->active()->orderBy('id');
            }
        ])->get()->filter(fn($c) => $c->questions->count() > 0)->values();

        // 2) Build header rows
        $header1 = [];
        $header2 = [];

        // User section (4 cols)
        $header1 = array_merge($header1, ['کاربر', '', '', '']);
        $header2 = array_merge($header2, ['کد پرسنلی', 'نام', 'نام خانوادگی', 'مرکز هزینه']);
        $this->topHeaderMerges[] = ['startCol' => 1, 'endCol' => 4, 'label' => 'کاربر'];

        // Evaluator section (4 cols)
        $startEval = count($header1) + 1; // 5
        $header1 = array_merge($header1, ['ارزیاب', '', '', '']);
        $header2 = array_merge($header2, ['مرکز هزینه', 'نام', 'نام خانوادگی', 'کد پرسنلی']);
        $this->topHeaderMerges[] = ['startCol' => $startEval, 'endCol' => $startEval + 3, 'label' => 'ارزیاب'];

        // Categories (variable)
        $currentCol = count($header1) + 1; // next after evaluator
        foreach ($categories as $index => $cat) {
            $qCount = $cat->questions->count();
            if ($qCount === 0) {
                continue;
            }
            $label = $cat->name ?? ('دسته ' . ($index + 1));
            // First row header for this category spans qCount columns
            $header1[] = $label;
            for ($i = 1; $i < $qCount; $i++) {
                $header1[] = '';
            }
            // Second row subheaders: سوال 1..N
            for ($i = 1; $i <= $qCount; $i++) {
                $header2[] = 'سوال ' . $i;
            }
            $this->topHeaderMerges[] = [
                'startCol' => $currentCol,
                'endCol' => $currentCol + $qCount - 1,
                'label' => $label,
            ];
            $currentCol += $qCount;
        }

        $this->data[] = $header1;
        $this->data[] = $header2;

        // 3) Load evaluatees for given month/year with relations
        $evaluatees = Evaluatee::whereHas('evaluator.evaluation', function ($query) {
            $query->where('month', $this->month)->where('year', $this->year);
        })->with([
                    'user.profile.costCenter',
                    'evaluator.user.profile.costCenter',
                    'scores.question',
                ])->get();

        // Group by user to merge user columns across multiple evaluator rows
        $groups = $evaluatees->groupBy('user_id');

        $rowPointer = 3; // first data row in Excel
        foreach ($groups as $userId => $rows) {
            $groupCount = $rows->count();
            $startRow = $rowPointer;

            foreach ($rows as $evaluatee) {
                $user = $evaluatee->user;
                $userCostCenter = optional(optional($user->profile)->costCenter)->name;

                $evaluator = $evaluatee->evaluator?->user;
                $evalCostCenter = optional(optional($evaluatee->evaluator?->user?->profile)->costCenter)->name;

                $record = [
                    // User block
                    $user?->personnel_code,
                    $user?->first_name,
                    $user?->last_name,
                    $userCostCenter,

                    // Evaluator block
                    $evalCostCenter,
                    $evaluator?->first_name,
                    $evaluator?->last_name,
                    $evaluator?->personnel_code,
                ];

                // Prepare score map for quick lookup
                $scoreByQuestion = collect($evaluatee->scores)->keyBy('question_id');

                foreach ($categories as $cat) {
                    $i = 1;
                    foreach ($cat->questions as $q) {
                        $score = optional($scoreByQuestion->get($q->id))->score;
                        $record[] = $score ?? '';
                        $i++;
                    }
                }

                $this->data[] = $record;
                $rowPointer++;
            }

            // register merge block for user columns if more than one evaluator row
            if ($groupCount > 1) {
                $this->userRowMerges[] = ['start' => $startRow, 'end' => $rowPointer - 1];
            }
        }

        // Write header labels into merged cells (the merge itself is in AfterSheet)
        foreach ($this->topHeaderMerges as $merge) {
            $this->data[0][$merge['startCol'] - 1] = $merge['label'];
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


