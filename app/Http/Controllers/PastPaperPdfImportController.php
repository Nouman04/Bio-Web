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
 * this receives one Quill-ready HTML string per question plus the cover page
 * details. Questions belong to the course only, with no chapter yet; the
 * citation hangs off a link to the course.
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
            'questions.*.html' => ['required', 'string'],
            'questions.*.marks' => ['nullable', 'numeric', 'min:0'],
            'questions.*.answer' => ['nullable', 'string'],
        ]);

        $categoryId = QuestionCategory::firstOrCreate(['type' => 'theory'])->id;

        $cover = $data['cover'] ?? [];
        $month = self::SESSIONS[mb_strtolower($cover['session'] ?? '')] ?? null;
        $date = ($month && ! empty($cover['year'])) ? Carbon::createFromDate($cover['year'], $month, 1)->toDateString() : null;

        // Question number => question id for this PDF's questions already in this
        // course, so a re-upload is harmless (and can still add the answers).
        $existing = PastPaperReference::with('link:id,question_id')
            ->where('source', $data['filename'])
            ->whereHas('link', fn ($q) => $q->where('questionable_type', Course::class)->where('questionable_id', $course->id))
            ->get()
            ->mapWithKeys(fn ($ref) => [$ref->question_no => $ref->link->question_id])
            ->all();

        $imported = 0;
        $skipped = 0;
        $answered = 0;

        DB::transaction(function () use ($data, $course, $categoryId, $cover, $date, $existing, &$imported, &$skipped, &$answered) {
            foreach ($data['questions'] as $q) {
                // From the mark scheme; stored like a theory answer typed in the editor.
                $answer = $q['answer'] ?? null;

                if (isset($existing[(string) $q['number']])) {
                    $questionId = $existing[(string) $q['number']];
                    if ($answer && ! QuestionAnswer::where('question_bank_id', $questionId)->exists()) {
                        QuestionAnswer::create(['question_bank_id' => $questionId, 'description' => $answer]);
                        $answered++;
                    }
                    $skipped++;
                    continue;
                }

                $question = QuestionBank::create([
                    'course_id' => $course->id,
                    'question_categories_id' => $categoryId,
                    'question' => $this->storeImages($q['html']),
                ]);

                if ($answer) {
                    QuestionAnswer::create(['question_bank_id' => $question->id, 'description' => $answer]);
                    $answered++;
                }

                $link = QuestionableType::create([
                    'question_id' => $question->id,
                    'questionable_type' => Course::class,
                    'questionable_id' => $course->id,
                ]);

                // The citation's columns are required; a PDF without a readable
                // cover still imports its questions, just uncited.
                if ($date && ! empty($cover['paperNo']) && isset($q['marks'])) {
                    PastPaperReference::create([
                        'questionable_type_id' => $link->id,
                        'date' => $date,
                        'paper_no' => $cover['paperNo'],
                        'question_no' => (string) $q['number'],
                        'marks' => $q['marks'],
                        'source' => $data['filename'],
                    ]);
                }

                $imported++;
            }
        });

        return response()->json(['imported' => $imported, 'skipped' => $skipped, 'answered' => $answered, 'date' => $date]);
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
