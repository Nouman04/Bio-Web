<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\SavedContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The bookmark button. Saving is a student thing only — the admin panel has no
 * such feature — so this lives behind the student middleware with the rest.
 */
class SavedContentController extends Controller
{
    /**
     * Saves or unsaves one piece of content, and says which it ended up.
     */
    public function toggle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(SavedContent::TYPES))],
            'uuid' => ['required', 'string'],
        ]);

        $model = SavedContent::TYPES[$data['type']];
        $record = $model::where('uuid', $data['uuid'])->firstOrFail();

        $existing = SavedContent::where('user_id', $request->user()->id)
            ->where('contentable_type', $model)
            ->where('contentable_id', $record->id)
            ->first();

        if ($existing) {
            $existing->delete();

            return response()->json(['saved' => false]);
        }

        SavedContent::create([
            'user_id' => $request->user()->id,
            'contentable_type' => $model,
            'contentable_id' => $record->id,
        ]);

        return response()->json(['saved' => true]);
    }
}
