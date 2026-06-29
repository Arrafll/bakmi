<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RestaurantTable extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'qr_token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id');
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    /**
     * Absolute URL encoded in the QR code.
     */
    public function getQrUrlAttribute(): string
    {
        return url('/order/' . $this->qr_token);
    }

    /**
     * Public URL to the generated SVG file.
     */
    public function getQrCodeUrlAttribute(): string
    {
        return asset('storage/qrcodes/' . $this->qr_token . '.svg');
    }

    /**
     * Absolute filesystem path to the SVG file.
     */
    public function getQrCodePathAttribute(): string
    {
        return storage_path('app/public/qrcodes/' . $this->qr_token . '.svg');
    }

    public function qrCodeExists(): bool
    {
        return file_exists($this->qr_code_path);
    }

    // ── Actions ──────────────────────────────────────────────────────────────

    /**
     * Issue a new secure random token.
     * The QR file for the old token should be deleted and regenerated.
     */
    public function regenerateToken(): void
    {
        // Remove old QR file
        if ($this->qrCodeExists()) {
            @unlink($this->qr_code_path);
        }

        $this->update(['qr_token' => Str::random(40)]);
    }

    // ── Factory helper ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::creating(function (self $table) {
            if (empty($table->qr_token)) {
                $table->qr_token = Str::random(40);
            }
        });
    }
}
