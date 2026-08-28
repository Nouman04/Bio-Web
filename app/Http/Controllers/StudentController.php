<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ProgressService;
use App\Services\StudentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

/**
 * The staff-side roster: who is subscribed to the signed-in user's courses,
 * how far through they are, and what of theirs is waiting to be marked.
 *
 * This decides what the request asked for and what to send back; StudentService
 * decides what it means for the database.
 */
class StudentController extends Controller
{
    public function __construct(
        private readonly StudentService $students,
        private readonly ProgressService $progress,
    ) {
    }

    /**
     * The listing page. The table itself is loaded by DataTables from the
     * `students.data` endpoint below.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        return view('students.index', [
            'courses' => $this->students->coursesFor($user)->orderBy('title')->get(['id', 'uuid', 'title']),
            'stats' => $this->students->stats($user),
            'filters' => [
                'search' => $request->input('search', ''),
                'course' => $request->input('course', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    /**
     * Server-side DataTables source for the roster.
     *
     * DataTables owns this response shape — it renders Blade partials into
     * cells rather than returning models — so an API resource has nothing to
     * describe here.
     */
    public function data(Request $request): JsonResponse
    {
        // DataTables reserves `search` for its own box, so ours arrives as
        // `search_term`.
        $students = $this->students->listing($request->user(), [
            'search' => $request->input('search_term'),
            'course' => $request->input('course'),
            'status' => $request->input('status'),
        ]);

        $table = DataTables::eloquent($students)
            ->addColumn('student_cell', fn (User $student) => view('students.partials.student-cell', compact('student'))->render())
            ->addColumn('email_cell', fn (User $student) => view('students.partials.email-cell', compact('student'))->render())
            ->addColumn('joined_cell', fn (User $student) => view('students.partials.joined-cell', compact('student'))->render())
            ->addColumn('pending_cell', fn (User $student) => view('students.partials.pending-cell', compact('student'))->render())
            ->addColumn('action', fn (User $student) => view('students.partials.actions', compact('student'))->render())
            ->orderColumn('student_cell', 'name $1')
            ->orderColumn('joined_cell', 'created_at $1')
            ->orderColumn('pending_cell', 'pending_quizzes_count $1')
            ->rawColumns(['student_cell', 'email_cell', 'joined_cell', 'pending_cell', 'action'])
            ->only(['student_cell', 'email_cell', 'joined_cell', 'pending_cell', 'action'])
            ->toJson();

        // Never let a proxy or the browser replay an old page of rows.
        return $table->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    /**
     * One student: the courses they are subscribed to, how far through each
     * one they are, and what of theirs is waiting to be marked.
     */
    public function show(Request $request, string $student)
    {
        $record = $this->find($request, $student);

        return view('students.show', [
            'student' => $record,
            'courses' => $this->students->coursesOf($record, $request->user()),
            'pending' => $this->students->pendingQuizzesOf($record, $request->user()),
        ]);
    }

    /**
     * Everything of this student's that is waiting on a marker. Each row leads
     * to the marking page, which is where the marks are actually given.
     */
    public function pendingQuizzes(Request $request, string $student)
    {
        $record = $this->find($request, $student);

        return view('students.pending-quizzes', [
            'student' => $record,
            'attempts' => $this->students->pendingQuizzesOf($record, $request->user()),
        ]);
    }

    /**
     * The student, if the signed-in user answers for a course they subscribe
     * to. Anyone else gets a 404 rather than a hint that the account exists.
     */
    private function find(Request $request, string $uuid): User
    {
        $student = User::where('uuid', $uuid)->firstOrFail();

        abort_unless($this->students->mayView($request->user(), $student), 404);

        return $student;
    }
}
