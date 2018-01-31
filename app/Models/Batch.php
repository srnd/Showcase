<?php

namespace Showcase\Models;

use Carbon\Carbon;

class Batch extends Model
{
    protected $dates = ['StartsAt'];

    public function getRouteKeyName()
    {
        return 'Webname';
    }

    public function Events()
    {
        return $this->hasMany(Event::class, 'BatchId', 'Id');
    }

    public function getIsFutureAttribute()
    {
        return Carbon::now()->addHours(-12)->lte($this->StartsAt);
    }

    public function getEndsAtAttribute()
    {
        return $this->StartsAt->addHours(24);
    }

    public function getIsCurrentAttribute()
    {
        return Carbon::now()->addHours(-12)->lte($this->EndsAt) && !$this->IsFuture;
    }

    public static function Current()
    {
        return Batch
                ::where('StartsAt', '<', Carbon::now())
                ->where('StartsAt', '>', Carbon::now()->addHours(-12))
                ->first();
    }

    public static function GetFromWebname(string $webname): self
    {
        return self::where('Webname', '=', $webname)->firstOrFail();
    }
}
