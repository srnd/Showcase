<?php

namespace Showcase\Models;

class Photo extends Model
{
    protected $fillable = ['EventId', 'Url', 'UrlLarge', 'UrlMedium', 'UrlSmall'];
    public function Event()
    {
        return $this->belongsTo(Event::class, 'EventId', 'Id');
    }
}
