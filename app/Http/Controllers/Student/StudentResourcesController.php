<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SavedContent;
use Illuminate\Http\Request;

class StudentResourcesController extends Controller
{
    /**
     * Everything the student has bookmarked, grouped into a panel per kind.
     *
     * Saves whose content has since been deleted are dropped — they have
     * nowhere to link to.
     */
    public function index(Request $request)
    {
        $types = SavedContent::TYPES;
        $type = array_key_exists((string) $request->input('type'), $types)
            ? $request->input('type')
            : null;

        $saved = SavedContent::where('user_id', $request->user()->id)
            ->with('contentable.chapter.course')
            ->latest('id')
            ->get()
            ->filter(fn (SavedContent $item) => $item->url !== null);

        return view('student.resources.index', [
            // Keyed by short name, in the order the panels are laid out.
            'groups' => $saved->groupBy('type_key'),
            'type' => $type,
            'total' => $saved->count(),
        ]);
    }
}
