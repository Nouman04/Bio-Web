<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\PastPaperReference;
use App\Models\QuestionAnswer;
use App\Models\QuestionableType;
use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Saves the questions of one past-paper PDF.
 *
 * The PDF itself is read in the browser (resources/js/past-paper-import.js);
 * this receives each question as a tree of parts (Q1 -> 1(a) -> 1(a)(i)), each
 * part with its own Quill-ready HTML, plus the cover page details.
 *
 * The question and each part become question_bank rows, nested by parent_id,
 * each with its label ("3(b)(i)") and marks; their words go to `questions`.
 * The question itself is linked to the course with one citation: paper,
 * session, question number, total marks. Parts get a chapter and topic later,
 * when the question worksheet (Excel) is imported. A part without marks is a
 * lead-in for the parts under it.
 */
class PastPaperPdfImportController extends Controller
{
    private const SESSIONS = ['february/march' => 3, 'may/june' => 6, 'october/november' => 11];

    public function store(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'filename' => ['required', 'string', 'max:255'],
            'cover.paperNo' => ['nullable', 'string', 'max:20'],
            'cover.session' => ['nullable', 'string', 'max:40'],
            'cover.year' => ['nullable', 'integer', 'between:1990,2100'],
            'questions' => ['required', 'array', 'min:1', 'max:100'],
            'questions.*.number' => ['required', 'integer', 'min:1'],
            'questions.*.marks' => ['nullable', 'numeric', 'min:0'],
            'questions.*.nodes' => ['required', 'array', 'min:1'],
            'questions.*.nodes.*.ref' => ['required', 'string', 'max:40'],
            'questions.*.nodes.*.parent' => ['nullable', 'string', 'max:40'],
            'questions.*.nodes.*.html' => ['present', 'nullable', 'string'],
            'questions.*.nodes.*.marks' => ['nullable', 'numeric', 'min:0'],
            'questions.*.nodes.*.answer' => ['nullable', 'string'],
        ]);

        $categoryId = QuestionCategory::firstOrCreate(['type' => 'theory'])->id;

        $cover = $data['cover'] ?? [];
        $month = self::SESSIONS[mb_strtolower($cover['session'] ?? '')] ?? null;
        $date = ($month && ! empty($cover['year'])) ? Carbon::createFromDate($cover['year'], $month, 1)->toDateString() : null;

        // Question number => question_bank id for this PDF's questions already
        // in this course, so a re-upload is harmless (and can still add answers).
        $existing = PastPaperReference::with('link:id,question_id')
            ->where('source', $data['filename'])
            ->whereHas('link.question', fn ($q) => $q->where('course_id', $course->id))
            ->get()
            ->mapWithKeys(fn ($ref) => [$ref->question_no => $ref->link->question_id])
            ->all();

        $imported = 0;
        $skipped = 0;
        $answered = 0;

        DB::transaction(function () use ($data, $course, $categoryId, $cover, $date, $existing, &$imported, &$skipped, &$answered) {
            foreach ($data['questions'] as $q) {
                if ($questionId = $existing[(string) $q['number']] ?? null) {
                    $answered += $this->addMissingAnswers($questionId, $q['nodes']);
                    $skipped++;
                    continue;
                }

                $answered += $this->storeQuestion($q, $course, $categoryId, $cover, $date, $data['filename']);
                $imported++;
            }
        });

        return response()->json(['imported' => $imported, 'skipped' => $skipped, 'answered' => $answered, 'date' => $date]);
    }

    /**
     * One question and its parts, all question_bank rows: the question at the
     * top, each part under its parent with its label and marks. Each row's
     * text goes to `questions` (only when it has any). Returns how many
     * answers were saved.
     */
    private function storeQuestion(array $q, Course $course, int $categoryId, array $cover, ?string $date, string $filename): int
    {
        $ids = [];
        $answered = 0;

        foreach ($q['nodes'] as $node) {
            $row = QuestionBank::create([
                'course_id' => $course->id,
                'parent_id' => $node['parent'] ? ($ids[$node['parent']] ?? null) : null,
                'part_ref' => $node['ref'],
                'marks' => $node['marks'] ?? null,
                'question_categories_id' => $categoryId,
                'question' => $this->storeImages($node['html'] ?? ''),
            ]);
            $ids[$node['ref']] = $row->id;

            // From the mark scheme; stored like a theory answer typed in the editor.
            if (! empty($node['answer'])) {
                QuestionAnswer::create(['question_bank_id' => $row->id, 'description' => $node['answer']]);
                $answered++;
            }
        }

        // The citation sits on the question itself; its parts are found
        // through parent_id.
        $link = QuestionableType::create([
            'question_id' => $ids[$q['nodes'][0]['ref']],
            'questionable_type' => Course::class,
            'questionable_id' => $course->id,
        ]);

        // The citation's columns are required; a PDF without a readable
        // cover still imports its questions, just uncited.
        if ($date && ! empty($cover['paperNo'])) {
            PastPaperReference::create([
                'questionable_type_id' => $link->id,
                'date' => $date,
                'paper_no' => $cover['paperNo'],
                'question_no' => (string) $q['number'],
                'marks' => $q['marks'] ?? array_sum(array_column($q['nodes'], 'marks')),
                'source' => $filename,
            ]);
        }

        return $answered;
    }

    /**
     * A re-upload with a mark scheme fills in answers the first import lacked.
     */
    private function addMissingAnswers(int $questionId, array $nodes): int
    {
        $rows = collect([QuestionBank::find($questionId)])->filter();
        for ($level = $rows; $level->isNotEmpty(); $rows = $rows->merge($level)) {
            $level = QuestionBank::whereIn('parent_id', $level->pluck('id'))->get();
        }
        $byRef = $rows->pluck('id', 'part_ref');
        $answered = 0;

        foreach ($nodes as $node) {
            $id = $byRef[$node['ref']] ?? null;
            if ($id && ! empty($node['answer']) && ! QuestionAnswer::where('question_bank_id', $id)->exists()) {
                QuestionAnswer::create(['question_bank_id' => $id, 'description' => $node['answer']]);
                $answered++;
            }
        }

        return $answered;
    }

    /**
     * Figures arrive as data: URLs; keep them as files so the question text
     * stays small.
     */
    private function storeImages(string $html): string
    {
        return preg_replace_callback('/src="data:image\/(png|jpeg);base64,([^"]+)"/', function ($m) {
            $path = 'past-papers/' . Str::uuid() . '.' . ($m[1] === 'jpeg' ? 'jpg' : 'png');
            Storage::disk('public')->put($path, base64_decode($m[2]));

            return 'src="' . asset('storage/' . $path) . '"';
        }, $html);
    }
}
