<?php

namespace A17\Twill\Models;

use Illuminate\Database\Eloquent\Model;

class Tagged extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    public function getTable()
    {
        return config('twill.tagged_table', 'tagged');
    }
}
