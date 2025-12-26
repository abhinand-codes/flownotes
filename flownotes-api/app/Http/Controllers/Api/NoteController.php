<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->notes()->latest()->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
        ]);

        return $request->user()->notes()->create($data);
    }

    public function show(Note $note)
    {
        $this->authorizeNote($note);
        return $note;
    }

    public function update(Request $request, Note $note)
    {
        $this->authorizeNote($note);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'nullable|string',
        ]);

        $note->update($data);
        return $note;
    }

    public function destroy(Note $note)
    {
        $this->authorizeNote($note);
        $note->delete();
        return response()->noContent();
    }

    private function authorizeNote(Note $note): void
    {
        abort_unless($note->user_id === auth()->id(), 403);
    }
}
