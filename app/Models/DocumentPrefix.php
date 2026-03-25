<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class DocumentPrefix extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'prefix',
        'separator',
        'format',
        'description',
        'status',
        'is_default',
        'current_sequence',
        'reset_period',
        'last_reset_at',
    ];

    protected $casts = [
        'is_default'       => 'boolean',
        'current_sequence' => 'integer',
        'last_reset_at'    => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(
                fn(string $eventName) => "Document Prefix [{$this->name}] has been {$eventName}"
            );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(\App\Models\Document::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function generateNumber(): string
    {
        $now = Carbon::now();

        $this->checkReset($now);

        $this->current_sequence = ($this->current_sequence ?? 0) + 1;

        $format = $this->format;
        $format = str_replace('{PREFIX}', $this->prefix,              $format);
        $format = str_replace('{SEP}',    $this->separator ?? '-',    $format);
        $format = str_replace('{YEAR}',   $now->format('Y'),          $format);
        $format = str_replace('{MONTH}',  $now->format('m'),          $format);
        $format = str_replace('{DAY}',    $now->format('d'),          $format);

        $sequence = $this->current_sequence;
        $format = preg_replace_callback('/\{SEQ(?::(\d+))?\}/', function ($matches) use ($sequence) {
            $pad = isset($matches[1]) ? (int) $matches[1] : 4;
            return str_pad($sequence, $pad, '0', STR_PAD_LEFT);
        }, $format);

        $this->last_reset_at = $now;
        $this->saveQuietly(); 
        return $format;
    }

    protected function checkReset(Carbon $now): void
    {
        if (! $this->last_reset_at) {
            $this->current_sequence = 0;
            $this->last_reset_at    = $now;
            $this->saveQuietly();
            return;
        }

        $shouldReset = match ($this->reset_period) {
            'year'  => $now->year  !== $this->last_reset_at->year,
            'month' => $now->year  !== $this->last_reset_at->year
                    || $now->month !== $this->last_reset_at->month,
            'day'   => ! $now->isSameDay($this->last_reset_at),
            default => false,
        };

        if ($shouldReset) {
            $this->current_sequence = 0;
            $this->last_reset_at    = $now;
            $this->saveQuietly();
        }
    }
}