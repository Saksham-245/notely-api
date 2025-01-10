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
     *     description="Retrieves a paginated list of notes for the authenticated user",
     *     tags={"Notes"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of notes",
     *         @OA\JsonContent(
     *             @OA\Property(property="current_page", type="integer", example=1),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Note")),
     *             @OA\Property(property="first_page_url", type="string"),
     *             @OA\Property(property="from", type="integer"),
     *             @OA\Property(property="last_page", type="integer"),
     *             @OA\Property(property="last_page_url", type="string"),
     *             @OA\Property(property="links", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="next_page_url", type="string", nullable=true),
     *             @OA\Property(property="path", type="string"),
     *             @OA\Property(property="per_page", type="integer", example=10),
     *             @OA\Property(property="prev_page_url", type="string", nullable=true),
     *             @OA\Property(property="to", type="integer"),
     *             @OA\Property(property="total", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated")
     *         )
     *     )
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
     *     description="Creates a new note for the authenticated user",
     *     tags={"Notes"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title", "content"},
     *             @OA\Property(property="title", type="string", example="My Note Title"),
     *             @OA\Property(property="content", type="string", example="Note content goes here")
     *         )
     *     ),
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
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
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
     *     description="Retrieves details of a specific note",
     *     tags={"Notes"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Note ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note details",
     *         @OA\JsonContent(
     *             @OA\Property(property="note", ref="#/components/schemas/Note")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Note not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
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
     *     description="Updates an existing note",
     *     tags={"Notes"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Note ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="Updated Note Title"),
     *             @OA\Property(property="content", type="string", example="Updated note content")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="s", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Note updated successfully"),
     *             @OA\Property(property="note", ref="#/components/schemas/Note")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Note not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="s", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
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
     *     description="Deletes an existing note",
     *     tags={"Notes"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Note ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Note deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="s", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Note deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Note not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Note not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="s", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
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
     *     description="Search for notes by title",
     *     tags={"Notes"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         required=true,
     *         description="Search query",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Search results",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="notes",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Note")),
     *                 @OA\Property(property="first_page_url", type="string"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="last_page_url", type="string"),
     *                 @OA\Property(property="links", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="next_page_url", type="string", nullable=true),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer", example=10),
     *                 @OA\Property(property="prev_page_url", type="string", nullable=true),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="s", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An error occurred while searching for notes")
     *         )
     *     )
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
