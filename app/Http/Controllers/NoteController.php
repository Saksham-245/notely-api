<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/notes",
     *     summary="Get all notes",
     *     tags={"Notes"},
     *     @OA\Response(response=200, description="List of notes")
     * )
     */
    public function index(Request $request)
    {
        try {
            $notes = Note::where('user_id', $request->user()->id)->orderBy('created_at', 'desc')->paginate(10);
            return response()->json($notes);
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while fetching notes');
        }
    }

    /**
     * @OA\Post(
     *     path="/api/notes",
     *     summary="Create a new note",
     *     tags={"Notes"},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Note")),
     *     @OA\Response(
     *         response=201,
     *         description="Note created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="s", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Note created successfully"),
     *             @OA\Property(property="note", ref="#/components/schemas/Note")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="s", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Note creation failed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="s", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized: Cannot create notes for other users")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/notes/{id}",
     *     summary="Get a specific note",
     *     tags={"Notes"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Note ID", @OA\Schema(type="integer"))
     * )
     */
    public function show(Note $note)
    {
        try {
            return response()->json(['note' => $note]);
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while showing the note');
        }
    }

    /**
     * @OA\Put(
     *     path="/api/notes/{id}",
     *     summary="Update a specific note",
     *     tags={"Notes"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Note ID", @OA\Schema(type="integer"))
     * )
     */
    public function update(Request $request, Note $note)
    {
        try {
            $note->update($request->all());
            return response()->json(['s' => true, 'message' => 'Note updated successfully', 'note' => $note]);
        } catch (\Throwable $th) {
            throw new \Exception('An error occurred while updating the note');
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/notes/{id}",
     *     summary="Delete a specific note",
     *     tags={"Notes"},
     *     @OA\Parameter(name="id", in="path", required=true, description="Note ID", @OA\Schema(type="integer"))
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/notes/search",
     *     summary="Search for notes",
     *     tags={"Notes"},
     *     @OA\Parameter(name="query", in="query", required=true, description="Search query", @OA\Schema(type="string"))
     * )
     */
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
