<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentChaptersController extends Controller
{
    public function index(Request $request, $courseId)
    {
        return view('student.chapters.index', compact('courseId'));
    }

    public function show(Request $request, $courseId, $chapterId)
    {
        return view('student.chapters.show', compact('courseId', 'chapterId'));
    }

    public function notes(Request $request, $courseId, $chapterId)
    {
        return view('student.chapters.notes.index', compact('courseId', 'chapterId'));
    }

    public function showNote(Request $request, $courseId, $chapterId, $noteId)
    {
        return view('student.chapters.notes.show', compact('courseId', 'chapterId', 'noteId'));
    }

    public function flashcards(Request $request, $courseId, $chapterId)
    {
        return view('student.chapters.flashcards.index', compact('courseId', 'chapterId'));
    }

    public function showFlashcard(Request $request, $courseId, $chapterId, $flashcardId)
    {
        return view('student.chapters.flashcards.show', compact('courseId', 'chapterId', 'flashcardId'));
    }

    public function diagrams(Request $request, $courseId, $chapterId)
    {
        return view('student.chapters.diagrams.index', compact('courseId', 'chapterId'));
    }

    public function showDiagram(Request $request, $courseId, $chapterId, $diagramId)
    {
        return view('student.chapters.diagrams.show', compact('courseId', 'chapterId', 'diagramId'));
    }

    public function summaries(Request $request, $courseId, $chapterId)
    {
        return view('student.chapters.summaries.index', compact('courseId', 'chapterId'));
    }

    public function showSummary(Request $request, $courseId, $chapterId, $summaryId)
    {
        return view('student.chapters.summaries.show', compact('courseId', 'chapterId', 'summaryId'));
    }

    public function videos(Request $request, $courseId, $chapterId)
    {
        return view('student.chapters.videos.index', compact('courseId', 'chapterId'));
    }

    public function showVideo(Request $request, $courseId, $chapterId, $videoId)
    {
        return view('student.chapters.videos.show', compact('courseId', 'chapterId', 'videoId'));
    }
}
