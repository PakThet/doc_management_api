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
        'reset_period'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Document Prefix [{$this->name}] has been {$eventName}");
    }

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────────

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

        $format = $this->format;

        $format = str_replace('YYYY', $now->format('Y'), $format);
        $format = str_replace('MM', $now->format('m'), $format);
        $format = str_replace('DD', $now->format('d'), $format);

        $this->increment('current_sequence');

        $sequence = str_pad($this->current_sequence, 3, '0', STR_PAD_LEFT);

        $format = str_replace('XXX', $sequence, $format);

        return $this->prefix . $this->separator . $format;
    }

    protected function checkReset(Carbon $now)
    {
        if (!$this->last_reset_at) {
            $this->last_reset_at = $now;
            $this->save();
            return;
        }

        $reset = false;

        switch ($this->reset_period) {

            case 'year':
                $reset = $now->year != $this->last_reset_at->year;
                break;

            case 'month':
                $reset = $now->month != $this->last_reset_at->month
                    || $now->year != $this->last_reset_at->year;
                break;

            case 'day':
                $reset = !$now->isSameDay($this->last_reset_at);
                break;
        }

        if ($reset) {
            $this->current_sequence = 0;
            $this->last_reset_at = $now;
            $this->save();
        }
    }
}
