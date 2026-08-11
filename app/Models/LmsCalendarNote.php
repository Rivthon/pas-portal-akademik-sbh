<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsCalendarNote extends Model
{
    protected $table = 'lms_calendar_notes';

    protected $primaryKey = 'note_id';

    protected $fillable = [
        'owner_type', 'owner_id', 'title', 'description',
        'note_date', 'note_time', 'color',
    ];

    protected $casts = [
        'note_date' => 'date',
    ];
}
