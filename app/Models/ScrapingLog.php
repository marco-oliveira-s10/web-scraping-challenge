<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScrapingLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'type',
        'category',
        'message',
        'context',
        'occurred_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'context' => 'json',
        'occurred_at' => 'datetime',
    ];
    
    /**
     * Scope a query to only include logs of a given type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }
    
    /**
     * Scope a query to only include logs of a given category.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the formatted occurred_at timestamp.
     *
     * @return string
     */
    public function getFormattedOccurredAtAttribute()
    {
        return $this->occurred_at->format('M d, Y H:i:s');
    }

    /**
     * Get the status badge class based on the log type.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        return match($this->type) {
            'success' => 'bg-success',
            'error' => 'bg-danger',
            'warning' => 'bg-warning',
            'info' => 'bg-info',
            default => 'bg-secondary',
        };
    }
}