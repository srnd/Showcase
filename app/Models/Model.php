<?php

namespace Showcase\Models;

use Illuminate\Database\Eloquent;

class Model extends Eloquent\Model
{
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    public $incrementing = false;
    protected $primaryKey = 'Id';
    public function getKeyName() { return 'Id'; }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!isset($model->{$model->getKeyName()})) {
                $id = null;
                do {
                    $id = str_random(15);
                } while (static::where('Id', '=', $id)->exists());

                $model->{$model->getKeyName()} = $id;
            }
        });
    }
}
