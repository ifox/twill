<?php

namespace A17\Twill\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tag extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function tagged(): HasMany
    {
        return $this->hasMany(Tagged::class);
    }

    public function getTable()
    {
        return config('twill.tags_table', 'tags');
    }
}
