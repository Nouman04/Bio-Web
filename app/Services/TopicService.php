<?php

namespace App\Services;

use App\Models\Chapter;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Everything that happens to a topic: what to read, what to write, and what
 * has to happen alongside it.
 *
 * The controller decides what the request asked for; this decides what that
 * means for the database.
 */
class TopicService
{
    public function __construct(
        private readonly QuestionLinkService $questions,
        private readonly AttachmentService $attachments,
    ) {
    }

    /**
     * The chapter's topics, as the listing reads them.
     *
     * Parent and its chapter are eager-loaded because the edit trigger names
     * both; without it the grid queries twice per row.
     */
    public function listing(Chapter $chapter, ?string $search = null): Builder
    {
        return Topic::query()
            ->where('chapter_id', $chapter->id)
            ->with('parent:id,title,chapter_id', 'parent.chapter:id,title')
            ->withCount(['questionables', 'attachments'])
            ->when($search, fn (Builder $query, string $term) => $query->where('title', 'like', "%{$term}%"));
    }

    /**
     * Topics offered as a parent.
     *
     * Deliberately not scoped to one chapter: a topic often continues one
     * introduced elsewhere, so every chapter is searchable.
     */
    public function search(?string $term = null, ?string $excludeUuid = null, int $limit = 20): Collection
    {
        return Topic::query()
            ->when($term, fn (Builder $query, string $t) => $query->where('title', 'like', "%{$t}%"))
            // A topic can never be its own parent.
            ->when($excludeUuid, fn (Builder $query, string $uuid) => $query->where('uuid', '!=', $uuid))
            ->with('chapter:id,title,course_id', 'chapter.course:id,title')
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data   Validated payload.
     * @param  array<int, mixed>     $files  Uploaded attachments.
     */
    public function create(Chapter $chapter, array $data, array $files = []): Topic
    {
        return DB::transaction(function () use ($chapter, $data, $files) {
            $topic = Topic::create([
                'chapter_id' => $chapter->id,
                'parent_topic_id' => $data['parent_topic_id'] ?? null,
                'title' => $data['title'],
                'content' => $data['content'] ?? null,
            ]);

            $this->questions->sync($topic, $data);
            $this->attachments->store($topic, $files, 'topics');

            return $topic->fresh();
        });
    }

    /**
     * Uploads are added to the existing attachments rather than replacing them.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, mixed>     $files
     */
    public function update(Topic $topic, array $data, array $files = []): Topic
    {
        return DB::transaction(function () use ($topic, $data, $files) {
            $topic->update([
                'parent_topic_id' => $data['parent_topic_id'] ?? null,
                'title' => $data['title'],
                'content' => $data['content'] ?? null,
            ]);

            $this->questions->sync($topic, $data);
            $this->attachments->store($topic, $files, 'topics');

            return $topic->fresh();
        });
    }

    /**
     * Soft deletes the topic and detaches its questions, so the bank is not
     * left pointing at something nobody can reach.
     */
    public function delete(Topic $topic): void
    {
        DB::transaction(function () use ($topic) {
            $topic->questionables()->delete();
            $topic->delete();
        });
    }

    /**
     * One topic with everything its detail page shows.
     */
    public function forDetail(Topic $topic): Topic
    {
        return $topic->load('attachments', 'parent.chapter');
    }

    /**
     * The questions on a topic, shaped for the detail page.
     *
     * @return array<int, array<string, mixed>>
     */
    public function questions(Topic $topic): array
    {
        return $this->questions->linked($topic);
    }

    /**
     * The questions on a topic, shaped for the edit form's picker.
     */
    public function pickerQuestions(Topic $topic)
    {
        return $this->questions->forPicker($topic);
    }
}
