<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        // Simple paginated mock collection of students to match list.html
        $students = collect([
            ['id' => 1,  'name' => 'Elena Rostova',   'email' => 'elena.r@example.com',     'courses' => 'Advanced UI Design, +2', 'date' => '2023-10-12', 'batch' => 'fall23',   'status' => 'active'],
            ['id' => 2,  'name' => 'Marcus Chen',     'email' => 'm.chen@example.com',       'courses' => 'Data Structures',       'date' => '2023-11-05', 'batch' => 'fall23',   'status' => 'active'],
            ['id' => 3,  'name' => 'Sarah Jenkins',   'email' => 's.jenkins@example.com',    'courses' => 'Marketing 101, +1',     'date' => '2023-09-20', 'batch' => 'fall23',   'status' => 'inactive'],
            ['id' => 4,  'name' => 'Ahmed Khalid',    'email' => 'ahmed.k@example.com',      'courses' => 'Web Dev Bootcamp',      'date' => '2024-01-15', 'batch' => 'spring24', 'status' => 'active'],
            ['id' => 5,  'name' => 'Priya Sharma',    'email' => 'priya.s@example.com',      'courses' => 'Data Science, +3',      'date' => '2024-02-08', 'batch' => 'spring24', 'status' => 'active'],
            ['id' => 6,  'name' => 'James O\'Brien',  'email' => 'j.obrien@example.com',     'courses' => 'Cybersecurity Basics',  'date' => '2024-03-01', 'batch' => 'spring24', 'status' => 'inactive'],
            ['id' => 7,  'name' => 'Liu Yang',        'email' => 'liu.y@example.com',        'courses' => 'Machine Learning, +1',  'date' => '2024-04-10', 'batch' => 'spring24', 'status' => 'active'],
            ['id' => 8,  'name' => 'Fatima Al-Zahra', 'email' => 'fatima.z@example.com',     'courses' => 'Business Analytics',    'date' => '2024-05-19', 'batch' => 'fall24',   'status' => 'active'],
            ['id' => 9,  'name' => 'Noah Williams',   'email' => 'noah.w@example.com',       'courses' => 'Cloud Computing, +2',   'date' => '2024-06-03', 'batch' => 'fall24',   'status' => 'active'],
            ['id' => 10, 'name' => 'Amara Diallo',    'email' => 'amara.d@example.com',      'courses' => 'UX Research',           'date' => '2024-06-22', 'batch' => 'fall24',   'status' => 'active'],
            ['id' => 11, 'name' => 'Carlos Ruiz',     'email' => 'c.ruiz@example.com',       'courses' => 'React Advanced',        'date' => '2024-07-01', 'batch' => 'fall24',   'status' => 'active'],
            ['id' => 12, 'name' => 'Sophie Martin',   'email' => 'sophie.m@example.com',     'courses' => 'Project Management',    'date' => '2024-07-15', 'batch' => 'fall24',   'status' => 'inactive'],
        ]);

        // Filter by Search query
        if ($search = $request->input('search')) {
            $students = $students->filter(function ($s) use ($search) {
                return stripos($s['name'], $search) !== false || stripos($s['email'], $search) !== false;
            });
        }

        // Filter by Course
        if ($course = $request->input('course')) {
            $students = $students->filter(function ($s) use ($course) {
                return stripos($s['courses'], $course) !== false;
            });
        }

        // Filter by Date Range
        if ($dateFrom = $request->input('date_from')) {
            $students = $students->filter(function ($s) use ($dateFrom) {
                return $s['date'] >= $dateFrom;
            });
        }

        if ($dateTo = $request->input('date_to')) {
            $students = $students->filter(function ($s) use ($dateTo) {
                return $s['date'] <= $dateTo;
            });
        }

        // Filter by Batch
        if ($batch = $request->input('batch')) {
            $students = $students->filter(function ($s) use ($batch) {
                return $s['batch'] === $batch;
            });
        }

        // Filter by Status
        if ($status = $request->input('status')) {
            $students = $students->filter(function ($s) use ($status) {
                return $s['status'] === $status;
            });
        }

        // Simple sorting
        $sortBy = $request->input('sort', 'name');
        $sortDir = $request->input('direction', 'asc');

        if ($sortDir === 'desc') {
            $students = $students->sortByDesc($sortBy);
        } else {
            $students = $students->sortBy($sortBy);
        }

        // Calculate count metrics
        $totalCount = $students->count();
        $activeCount = $students->where('status', 'active')->count();
        $inactiveCount = $students->where('status', 'inactive')->count();

        // Paginate manually for display (10 per page)
        $page = (int) $request->input('page', 1);
        $perPage = 10;
        $paginated = $students->forPage($page, $perPage);
        $totalPages = max(1, ceil($totalCount / $perPage));

        return view('students.index', [
            'students' => $paginated,
            'totalCount' => $totalCount,
            'activeCount' => $activeCount,
            'inactiveCount' => $inactiveCount,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
            'filters' => [
                'search' => $search ?? '',
                'course' => $course ?? '',
                'date_from' => $dateFrom ?? '',
                'date_to' => $dateTo ?? '',
                'batch' => $batch ?? '',
                'status' => $status ?? '',
                'sort' => $sortBy,
                'direction' => $sortDir,
            ]
        ]);
    }
}
