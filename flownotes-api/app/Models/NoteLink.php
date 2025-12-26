<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteLink extends Model
{
    protected $fillable = ['source_note_id', 'target_note_id'];
}
