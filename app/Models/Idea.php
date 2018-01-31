<?php

namespace Showcase\Models;

class Idea extends Model
{
    protected $fillable = ['Pitch', 'Type'];
    const Types = ['app', 'game', 'site', 'hardware', 'other'];
    public function Event()
    {
        return $this->belongsTo(Event::class, 'EventId', 'Id');
    }
}
