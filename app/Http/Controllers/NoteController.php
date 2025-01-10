<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        try {
            $notes = Note::where('user_id', $request->user()->id)->orderBy('created_at', 'desc')->paginate(10);
            return response()->json($notes);
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while fetching notes');
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string',
                'content' => 'required|string'
            ]);

            $note = Note::create([
                'title' => $request->title,
                'content' => $request->content,
                'user_id' => $request->user()->id
            ]);
            if (!$note) {
                return response()->json(['s' => false, 'message' => 'Note creation failed'], 400);
            }
            // Verify the note belongs to the authenticated user
            if ($note->user_id !== $request->user()->id) {
                $note->delete();
                return response()->json(['s' => false, 'message' => 'Unauthorized: Cannot create notes for other users'], 403);
            }
            return response()->json(['s' => true, 'message' => 'Note created successfully', 'note' => $note], 201);
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while creating the note');
        }
    }

    public function show(Note $note)
    {
        try {
            return response()->json(['note' => $note]);
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while showing the note');
        }
    }

    public function update(Request $request, Note $note)
    {
        try {
            $note->update($request->all());
            return response()->json(['s' => true, 'message' => 'Note updated successfully', 'note' => $note]);
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while updating the note');
        }
    }

    public function destroy(Request $request, Note $note)
    {
        try {
            if ($note->user_id !== $request->user()->id) {
                return response()->json(['s' => false, 'message' => 'Unauthorized'], 403);
            }
            $note->delete();
            return response()->json(['s' => true, 'message' => 'Note deleted successfully']);
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while deleting the note');
        }
    }

    public function search(Request $request)
    {
        try {
            $notes = Note::where('user_id', $request->user()->id)
                ->where('title', 'like', '%' . $request->query('query') . '%')
                ->orderBy('created_at', 'desc')
                ->paginate(10);
            return response()->json(['notes' => $notes]);
        } catch (\Exception $e) {
            return response()->json(['s' => false, 'message' => 'An error occurred while searching for notes'], 500);
            throw new \Exception('An error occurred while searching for notes');
        }
    }
}
