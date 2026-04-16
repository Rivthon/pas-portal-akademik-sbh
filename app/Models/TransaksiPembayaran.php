<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiPembayaran extends Model
{
    use HasFactory;

    protected $table = 'transaksi_pembayaran';
    protected $primaryKey = 'transaksi_id';
    public $timestamps = true;
    
    protected $fillable = [
        'mahasiswa_id',
        'tagihan_mahasiswa_id',
        'nominal_bayar',
        'tanggal_bayar',
        'bukti_bayar',
        'keterangan',
        'status_verifikasi',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function tagihan()
    {
        return $this->belongsTo(TagihanMahasiswa::class, 'tagihan_mahasiswa_id');
    }
}
