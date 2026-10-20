<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeachingHonorPayment extends Model
{
    protected $fillable = [
        'batch_id',
        'dosen_id',
        'ta_id',
        'period_start',
        'period_end',
        'meeting_type',
        'meeting_id',
        'schedule_id',
        'meeting_date',
        'course_name',
        'study_program_name',
        'class_name',
        'topic',
        'paid_at',
        'paid_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'meeting_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'dosen_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }
}
