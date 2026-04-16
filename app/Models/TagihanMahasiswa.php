<?php
namespace App\Models;

use App\Models\TenorPembayaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TagihanMahasiswa extends Model
{
    use HasFactory;
    protected $table = 'tagihan_mahasiswa'; // Pastikan nama tabel sesuai
    protected $primaryKey = 'id';   // Pastikan primary key sesuai
    public $timestamps = true;
    protected $fillable = [
        'mahasiswa_id',
        'semester',
        'ta_id',
        'tenor_pembayaran_id',
        'jumlah_tagihan',
        'jatuh_tempo',
        'status',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class,'mahasiswa_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAkademik::class, 'ta_id');
    }

    public function tenorPembayaran()
    {
        return $this->belongsTo(TenorPembayaran::class, 'tenor_pembayaran_id');
    }
    
    public function transaksi()
    {
        return $this->hasMany(TransaksiPembayaran::class, 'tagihan_mahasiswa_id', 'id')->orderBy('tanggal_bayar', 'desc');
    }
}