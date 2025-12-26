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

        $note = $request->user()->notes()->create($data);
        $this->syncWikilinks($note, $data['content'] ?? '');
        return $note;
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
        if (isset($data['content'])) {
            $this->syncWikilinks($note, $data['content']);
        }
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
    public function autosave(Request $request, Note $note)
    {
        $this->authorizeNote($note);

        $data = $request->validate([
            'content' => 'nullable|string',
        ]);

        $note->update(['content' => $data['content'] ?? '']);
        $this->syncWikilinks($note, $data['content'] ?? '');

        return response()->json(['saved_at' => now()]);
    }

    private function syncWikilinks(Note $note, string $content): void
    {
        preg_match_all('/\[\[(.*?)\]\]/', $content, $matches);
        $titles = array_unique($matches[1]);

        $linkedNoteIds = [];
        foreach ($titles as $title) {
            $title = trim($title);
            if (empty($title))
                continue;

            $linkedNote = Note::firstOrCreate(
                ['user_id' => $note->user_id, 'title' => $title],
                ['content' => '']
            );
            $linkedNoteIds[] = $linkedNote->id;
        }

        $note->outgoingLinks()->sync($linkedNoteIds);
    }
}
