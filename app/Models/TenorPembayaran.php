<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenorPembayaran extends Model
{
    use HasFactory;

    protected $table = 'tenor_pembayaran';
    protected $primaryKey = 'id';   // Pastikan primary key sesuai
    public $timestamps = true;
    protected $fillable = ['semester', 'tenor', 'persentase', 'batas_waktu', 'tahun_masuk', 'gelombang_id'];

    // Menampilkan persentase dalam format yang lebih rapi
    public function getPersentaseFormattedAttribute()
    {
        return $this->persentase . '%';
    }

    public function tenorPembayaran()
    {
        return $this->hasMany(TenorPembayaran::class, 'id');
    }

    public function gelombang()
    {
        return $this->belongsTo(Gelombang::class, 'gelombang_id');
    }

}