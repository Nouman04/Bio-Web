<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Chapter;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Note;
use App\Models\Quiz;
use App\Models\Topic;
use App\Models\User;
use App\Models\UserCourseProgress;
use App\Services\ModuleRegistrar;
use App\Services\ProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProgressTrackingTest extends TestCase
{
    use RefreshDatabase;

    private ProgressService $progress;

    private User $user;

    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        $this->progress = app(ProgressService::class);
        $this->user = $this->makeStudent();
        $this->course = $this->makeCourse();
    }

    /* ── Registry ───────────────────────────────────────────────────────── */

    public function test_content_is_registered_as_a_module_when_created(): void
    {
        $chapter = $this->makeChapter($this->course);
        $note = $this->makeNote($chapter);

        $module = $this->progress->moduleFor($note);

        $this->assertNotNull($module);
        $this->assertSame('note', $module->type);
        $this->assertSame($chapter->id, $module->chapter_id);
        $this->assertSame(1, $module->weight, 'modules default to a weight of 1');
    }

    public function test_deleted_content_stops_counting_but_keeps_its_progress(): void
    {
        $chapter = $this->makeChapter($this->course);
        $kept = $this->makeNote($chapter);
        $removed = $this->makeNote($chapter);

        $this->progress->complete($this->user, $this->progress->moduleFor($kept), 'manual');
        $this->progress->complete($this->user, $this->progress->moduleFor($removed), 'manual');

        $module = $this->progress->moduleFor($removed);
        $removed->delete();

        // Both were finished, so the chapter stays at 100% over what is left.
        $this->assertSame(100.0, $this->progress->chapterProgress($this->user, $chapter)['progress']);
        $this->assertSame(1, $this->progress->chapterProgress($this->user, $chapter)['total_weight']);

        // The completion itself survives, so restoring the note restores it.
        $this->assertDatabaseHas('user_module_progress', [
            'course_module_id' => $module->id,
            'is_completed' => true,
        ]);
    }

    /* ── Completion criteria ────────────────────────────────────────────── */

    public function test_a_video_completes_at_ninety_percent_watched(): void
    {
        $module = $this->makeModule($this->makeChapter($this->course), 'video');

        $partial = $this->progress->watched($this->user, $module, 89);
        $this->assertFalse($partial->is_completed, '89% watched is not finished');
        $this->assertSame(89, $partial->progress, 'the position is kept for next time');

        $done = $this->progress->watched($this->user, $module, 90);
        $this->assertTrue($done->is_completed);
        $this->assertSame('watched', $done->completed_via);
        $this->assertNotNull($done->completed_at);
    }

    public function test_a_quiz_completes_on_reaching_its_passing_score(): void
    {
        $chapter = $this->makeChapter($this->course);
        $quiz = $this->makeQuiz($chapter, passingScore: 7);

        $failed = $this->progress->attempted($this->user, $quiz, earned: 6, total: 10);
        $this->assertFalse($failed->is_completed);
        $this->assertSame(60, $failed->progress);

        $passed = $this->progress->attempted($this->user, $quiz, earned: 7, total: 10);
        $this->assertTrue($passed->is_completed);
        $this->assertSame('passed', $passed->completed_via);
    }

    public function test_a_failed_retake_does_not_undo_a_pass(): void
    {
        $quiz = $this->makeQuiz($this->makeChapter($this->course), passingScore: 7);

        $this->progress->attempted($this->user, $quiz, earned: 9, total: 10);
        $retake = $this->progress->attempted($this->user, $quiz, earned: 2, total: 10);

        $this->assertTrue($retake->is_completed);
    }

    public function test_reading_completes_only_after_the_minimum_duration(): void
    {
        $module = $this->makeModule($this->makeChapter($this->course), 'note');

        $this->assertNull(
            $this->progress->viewed($this->user, $module, ProgressService::MIN_VIEW_SECONDS - 1),
            'a glance is not a read'
        );
        $this->assertDatabaseCount('user_module_progress', 0);

        $read = $this->progress->viewed($this->user, $module, ProgressService::MIN_VIEW_SECONDS);
        $this->assertTrue($read->is_completed);
        $this->assertSame('viewed', $read->completed_via);
    }

    public function test_the_manual_checkbox_completes_and_uncompletes(): void
    {
        $module = $this->makeModule($this->makeChapter($this->course), 'guide');

        $this->assertTrue($this->progress->setManual($this->user, $module, true)->is_completed);

        $undone = $this->progress->setManual($this->user, $module, false);
        $this->assertFalse($undone->is_completed);
        $this->assertNull($undone->completed_at);
    }

    /* ── Formulas ───────────────────────────────────────────────────────── */

    public function test_chapter_progress_is_completed_weight_over_total_weight(): void
    {
        $chapter = $this->makeChapter($this->course);
        $a = $this->makeModule($chapter, 'note');
        $b = $this->makeModule($chapter, 'note');
        $this->makeModule($chapter, 'note');
        $this->makeModule($chapter, 'note');

        $this->progress->complete($this->user, $a, 'manual');
        $this->progress->complete($this->user, $b, 'manual');

        $totals = $this->progress->chapterProgress($this->user, $chapter);

        $this->assertSame(50.0, $totals['progress']);
        $this->assertSame(2, $totals['completed_weight']);
        $this->assertSame(4, $totals['total_weight']);
    }

    public function test_weight_makes_a_module_count_for_more(): void
    {
        $chapter = $this->makeChapter($this->course);
        $note = $this->makeModule($chapter, 'note');
        $final = $this->makeModule($chapter, 'quiz', weight: 3);

        // 1 of 4 by weight, though it is 1 of 2 by count.
        $this->progress->complete($this->user, $note, 'manual');
        $this->assertSame(25.0, $this->progress->chapterProgress($this->user, $chapter)['progress']);

        $this->progress->complete($this->user, $final, 'manual');
        $this->assertSame(100.0, $this->progress->chapterProgress($this->user, $chapter)['progress']);
    }

    public function test_course_progress_spans_every_chapter_rather_than_averaging_them(): void
    {
        $big = $this->makeChapter($this->course);
        $small = $this->makeChapter($this->course);

        // Nine modules in one chapter, one in the other.
        foreach (range(1, 9) as $i) {
            $this->makeModule($big, 'note');
        }
        $only = $this->makeModule($small, 'note');

        $this->progress->complete($this->user, $only, 'manual');

        // Averaging the chapters would give 50%; by weight it is 1 of 10.
        $this->assertSame(100.0, $this->progress->chapterProgress($this->user, $small)['progress']);
        $this->assertSame(10.0, $this->progress->courseProgress($this->user, $this->course)['progress']);
    }

    public function test_a_course_with_no_modules_is_zero_not_undefined(): void
    {
        $this->makeChapter($this->course);

        $this->assertSame(0.0, $this->progress->courseProgress($this->user, $this->course)['progress']);
    }

    public function test_one_users_progress_does_not_count_for_another(): void
    {
        $chapter = $this->makeChapter($this->course);
        $module = $this->makeModule($chapter, 'note');
        $other = $this->makeStudent();

        $this->progress->complete($this->user, $module, 'manual');

        $this->assertSame(100.0, $this->progress->chapterProgress($this->user, $chapter)['progress']);
        $this->assertSame(0.0, $this->progress->chapterProgress($other, $chapter)['progress']);
    }

    /* ── Cache ──────────────────────────────────────────────────────────── */

    public function test_completing_a_module_caches_the_chapter_and_course_rows(): void
    {
        $chapter = $this->makeChapter($this->course);
        $a = $this->makeModule($chapter, 'note');
        $this->makeModule($chapter, 'note');

        // The queue runs synchronously under test, so the job has already run.
        $this->progress->complete($this->user, $a, 'manual');

        $this->assertDatabaseHas('user_course_progress', [
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'chapter_id' => $chapter->id,
            'progress' => 50.00,
        ]);

        $courseRow = UserCourseProgress::where('user_id', $this->user->id)
            ->where('chapter_key', 0)
            ->first();

        $this->assertNotNull($courseRow, 'the course total is cached alongside the chapters');
        $this->assertSame(50.0, (float) $courseRow->progress);
        $this->assertSame(50.0, $this->progress->cachedCourseProgress($this->user, $this->course));
    }

    public function test_recalculating_restates_the_cache_from_the_progress_rows(): void
    {
        $chapter = $this->makeChapter($this->course);
        $module = $this->makeModule($chapter, 'note');
        $this->progress->complete($this->user, $module, 'manual');

        // Something knocks the cache out of step.
        UserCourseProgress::query()->update(['progress' => 3]);

        $this->progress->recalculate($this->user, $this->course);

        $this->assertSame(100.0, $this->progress->cachedCourseProgress($this->user, $this->course));
    }

    /* ── Endpoints ──────────────────────────────────────────────────────── */

    public function test_the_student_endpoints_record_progress(): void
    {
        $chapter = $this->makeChapter($this->course);
        $module = $this->makeModule($chapter, 'video');

        $this->actingAs($this->user)
            ->postJson("/student/progress/{$module->uuid}/watched", ['percent' => 95])
            ->assertOk()
            ->assertJsonPath('completed', true)
            // JSON has no int/float distinction, so compare loosely.
            ->assertJsonPath('chapter.progress', fn ($value) => (float) $value === 100.0);

        $this->actingAs($this->user)
            ->postJson("/student/progress/{$module->uuid}/manual", ['completed' => false])
            ->assertOk()
            ->assertJsonPath('completed', false);
    }

    public function test_the_progress_endpoints_are_closed_to_guests(): void
    {
        $module = $this->makeModule($this->makeChapter($this->course), 'note');

        $this->postJson("/student/progress/{$module->uuid}/viewed", ['seconds' => 30])
            ->assertUnauthorized();
    }

    /* ── Fixtures ───────────────────────────────────────────────────────── */

    /**
     * The student endpoints are behind the student middleware, so a plain user
     * is not enough.
     */
    private function makeStudent(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']));

        return $user->fresh();
    }

    private function makeCourse(): Course
    {
        $category = Category::create(['title' => 'Testing', 'slug' => 'testing-' . uniqid()]);

        return Course::create([
            'created_by' => $this->user->id,
            'category_id' => $category->id,
            'title' => 'Progress Course',
            'slug' => 'progress-course-' . uniqid(),
            'description' => '<p>A course.</p>',
        ]);
    }

    private function makeChapter(Course $course): Chapter
    {
        return Chapter::create([
            'course_id' => $course->id,
            'title' => 'Chapter ' . uniqid(),
            'chapter_number' => Chapter::where('course_id', $course->id)->count() + 1,
            'description' => 'A chapter.',
        ]);
    }

    private function makeNote(Chapter $chapter): Note
    {
        $topic = Topic::create([
            'chapter_id' => $chapter->id,
            'title' => 'Topic ' . uniqid(),
            'description' => 'A topic.',
        ]);

        return Note::create([
            'chapter_id' => $chapter->id,
            'topic_id' => $topic->id,
            'title' => 'Note ' . uniqid(),
            'type' => 'exam_notes',
            'content' => '<p>Some content.</p>',
        ]);
    }

    private function makeQuiz(Chapter $chapter, ?int $passingScore = null): Quiz
    {
        $quiz = Quiz::create([
            'title' => 'Quiz ' . uniqid(),
            'type' => 'mcqs',
            'passing_score' => $passingScore,
        ]);

        $quiz->chapters()->attach($chapter->id);

        // The observer fires on create, before the chapter is attached, so a
        // quiz is registered once it has somewhere to belong.
        app(ModuleRegistrar::class)->register($quiz);

        return $quiz;
    }

    /**
     * A module with no real content behind it, for tests about the arithmetic
     * rather than about any particular type.
     */
    private function makeModule(Chapter $chapter, string $type, int $weight = 1): CourseModule
    {
        return CourseModule::create([
            'chapter_id' => $chapter->id,
            'type' => $type,
            'moduleable_type' => CourseModule::TYPES[$type],
            'moduleable_id' => random_int(100000, 999999),
            'weight' => $weight,
        ]);
    }
}
