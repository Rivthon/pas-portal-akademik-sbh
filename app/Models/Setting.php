<?php

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    protected $fillable = ['name', 'logo', 'footer_name', 'copyright', 'favicon', 'edom_enabled', 'website_down'];

    protected function casts(): array
    {
        return [
            'edom_enabled' => 'boolean',
            'website_down' => 'boolean',
        ];
    }
}
