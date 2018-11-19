<?php

namespace Showcase\Models;

class Registration extends Model
{
    public function Event()
    {
        return $this->belongsTo(Event::class, 'EventId', 'Id');
    }

    public function Teams()
    {
        return $this->hasMany(Team::class, 'EventId', 'Id');
    }
}
