<?php

namespace Showcase\Models;

use Carbon\Carbon;

class Event extends Model
{
    public function Batch()
    {
        return $this->belongsTo(Batch::class, 'BatchId', 'Id');
    }

    public function getRouteKeyName()
    {
        return 'Webname';
    }

    public function Registrations()
    {
        return $this->hasMany(Registration::class, 'EventId', 'Id');
    }

    public function Ideas()
    {
        return $this->hasMany(Idea::class, 'EventId', 'Id');
    }

    public function Teams()
    {
        return $this->hasMany(Team::class, 'EventId', 'Id')->OrderBy('Sort', 'ASC')->OrderBy('CreatedAt', 'DESC');
    }

    public function getPresentingTeamsAttribute()
    {
        return $this->Teams->where('IsPresenting', '=', true);
    }

    public function getNonPresentingTeamsAttribute()
    {
        return $this->Teams->where('IsPresenting', '=', false);
    }

    public function Photos()
    {
        return $this->hasMany(Photo::class, 'EventId', 'Id')->orderBy('CreatedAt', 'ASC');
    }

    public function PhotosRandom()
    {
        return $this->hasMany(Photo::class, 'EventId', 'Id')->orderByRaw('RAND()');
    }

    public function getStartsAtAttribute()
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->Batch->StartsAt->format('Y-m-d H:i:s'), $this->Timezone);
    }

    public function getEndsAtAttribute()
    {
        return $this->StartsAt->addHours(24);
    }

    public function getIsInProgressAttribute()
    {
        return Carbon::now()->gte($this->StartsAt->addHours(-12))
            && Carbon::now()->lte($this->EndsAt->addHours(4));
    }

    public function getAreTeamsFormedAttribute()
    {
        return Carbon::now()->gte($this->StartsAt->addHours(1));
    }

    public function getArePresentationsStartingAttribute()
    {
        return Carbon::now()->gte($this->EndsAt->addHours(-5));
    }

    public static function GetCurrentForWebname(string $webname)
    {
        return self
            ::where('Webname', '=', $webname)
            ->where('BatchId', '=', Batch::Current()->Id)
            ->get();
    }

    public static function GetAllForWebname(string $webname)
    {
        return self::where('Webname', '=', $webname)->get(); // TODO(@tylermenezes): with
    }

    public static function GetAllForRegion(string $webname)
    {
        return self::where('Region', '=', $webname)->get(); // TODO(@tytlermenezes): with
    }

    public static function GetFromBatchNameAndWebname(string $batchName, string $webname): self
    {
        return self
            ::where('Webname', '=', $webname)
            ->where('BatchId', '=', Batch::GetFromWebname($batchName)->Id)
            ->firstOrFail();
    }
}
