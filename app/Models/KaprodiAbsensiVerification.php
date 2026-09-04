<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KaprodiAbsensiVerification extends Model
{
    protected $fillable = ['jadwal_id', 'program_studi_id', 'verified_by_dosen_id', 'verified_at', 'source_updated_at'];

    protected $casts = ['verified_at' => 'datetime', 'source_updated_at' => 'datetime'];

    public function verifier()
    {
        return $this->belongsTo(Dosen::class, 'verified_by_dosen_id', 'dosen_id');
    }
}
