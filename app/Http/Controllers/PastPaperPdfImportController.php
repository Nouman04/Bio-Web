<?php

namespace App\Http\Controllers;

use App\Models\Chapter;
use App\Models\Course;
use App\Models\PastPaperReference;
use App\Models\QuestionableType;
use App\Models\QuestionBank;
use App\Models\QuestionCategory;
use App\Models\Topic;
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
 * details, and files them like a worksheet row: question, topic link, citation.
 */
class PastPaperPdfImportController extends Controller
{
    private const TOPIC = 'Past Paper Questions';

    private const SESSIONS = ['february/march' => 3, 'may/june' => 6, 'october/november' => 11];

    public function store(Request $request, Course $course): JsonResponse
    {
        $data = $request->validate([
            'chapter_id' => ['required', 'integer'],
            'filename' => ['required', 'string', 'max:255'],
            'cover.paperNo' => ['nullable', 'string', 'max:20'],
            'cover.session' => ['nullable', 'string', 'max:40'],
            'cover.year' => ['nullable', 'integer', 'between:1990,2100'],
            'questions' => ['required', 'array', 'min:1', 'max:100'],
            'questions.*.number' => ['required', 'integer', 'min:1'],
            'questions.*.html' => ['required', 'string'],
            'questions.*.marks' => ['nullable', 'numeric', 'min:0'],
        ]);

        $chapter = Chapter::where('course_id', $course->id)->findOrFail($data['chapter_id']);
        $topic = Topic::firstOrCreate(['chapter_id' => $chapter->id, 'title' => self::TOPIC]);
        $categoryId = QuestionCategory::firstOrCreate(['type' => 'theory'])->id;

        $cover = $data['cover'] ?? [];
        $month = self::SESSIONS[mb_strtolower($cover['session'] ?? '')] ?? null;
        $date = ($month && ! empty($cover['year'])) ? Carbon::createFromDate($cover['year'], $month, 1)->toDateString() : null;

        // Question numbers of this PDF already in this chapter, so a re-upload is harmless.
        $existing = PastPaperReference::where('source', $data['filename'])
            ->whereHas('link', fn ($q) => $q->where('chapter_id', $chapter->id))
            ->pluck('question_no')
            ->all();

        $imported = 0;
        $skipped = 0;

        DB::transaction(function () use ($data, $chapter, $topic, $categoryId, $cover, $date, $existing, &$imported, &$skipped) {
            foreach ($data['questions'] as $q) {
                if (in_array((string) $q['number'], $existing, true)) {
                    $skipped++;
                    continue;
                }

                $question = QuestionBank::create([
                    'chapter_id' => $chapter->id,
                    'question_categories_id' => $categoryId,
                    'question' => $this->storeImages($q['html']),
                ]);

                $link = QuestionableType::create([
                    'chapter_id' => $chapter->id,
                    'question_id' => $question->id,
                    'questionable_type' => Topic::class,
                    'questionable_id' => $topic->id,
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

        return response()->json(['imported' => $imported, 'skipped' => $skipped, 'date' => $date]);
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
