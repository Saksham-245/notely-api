<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="Note",
 *     required={"title", "content"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="title", type="string", example="My Note Title"),
 *     @OA\Property(property="content", type="string", example="This is the content of my note"),
 *     @OA\Property(property="user_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-03-20T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="datetime", example="2024-03-20T12:00:00Z")
 * )
 */
class Note extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'content', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
