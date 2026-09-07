<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiSubmission extends Model
{
    public const TEMPORARY_BAAK_NOTE = '[PENERBITAN SEMENTARA BAAK] Nilai diterbitkan tanpa menunggu verifikasi Kaprodi.';

    protected $fillable = ['jadwal_id', 'program_studi_id', 'submitted_by_dosen_id', 'status', 'submitted_at', 'reviewed_by_dosen_id', 'reviewed_at', 'review_note'];

    protected $casts = ['submitted_at' => 'datetime', 'reviewed_at' => 'datetime'];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function submitter()
    {
        return $this->belongsTo(Dosen::class, 'submitted_by_dosen_id', 'dosen_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(Dosen::class, 'reviewed_by_dosen_id', 'dosen_id');
    }

    public function isTemporaryBaakApproval(): bool
    {
        return $this->status === 'approved'
            && $this->reviewed_by_dosen_id === null
            && str_starts_with((string) $this->review_note, '[PENERBITAN SEMENTARA BAAK]');
    }
}
