<?php

namespace App\Models;

use A17\Twill\Models\Behaviors\HasTranslation;
use A17\Twill\Models\Behaviors\HasSlug;
use A17\Twill\Models\Behaviors\HasMedias;
use A17\Twill\Models\Behaviors\HasRevisions;
use A17\Twill\Models\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    use HasTranslation, HasSlug, HasMedias, HasRevisions;

    protected $fillable = [
        'published',
        'name',
        'bio',
    ];

    public $translatedAttributes = [
        'name',
        'bio',
    ];

    public $slugAttributes = [
        'name',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
