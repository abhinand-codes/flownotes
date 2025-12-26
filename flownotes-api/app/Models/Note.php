<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Note extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'title', 'content'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function outgoingLinks()
    {
        return $this->belongsToMany(Note::class, 'note_links', 'source_note_id', 'target_note_id');
    }

    public function incomingLinks()
    {
        return $this->belongsToMany(Note::class, 'note_links', 'target_note_id', 'source_note_id');
    }
}
