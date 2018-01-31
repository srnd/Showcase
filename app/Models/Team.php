<?php

namespace Showcase\Models;

class Team extends Model
{
    protected $fillable = ['EventId', 'Sort', 'Name', 'Description', 'Type', 'Link', 'PhotoUrl', 'PhotoUrlLarge', 'PhotoUrlMedium', 'PhotoUrlSmall'];
    const Types = ['app', 'game', 'site', 'hardware', 'other'];

    public function Event()
    {
        return $this->belongsTo(Event::class, 'EventId', 'Id');
    }

    public function Members()
    {
        return $this->hasMany(Registration::class); // TODO(@tylermenezes): Create and link pivot model
    }
}
