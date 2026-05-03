<?php

namespace A17\Twill\Models;

use Illuminate\Database\Eloquent\Builder;

interface TaggableInterface
{
    public function allTags(): Builder;

    public function setTags(array|string $tags): void;

    public function tag(array|string $tags): void;

    public function untag(array|string $tags): void;

    public function setSlugGenerator(callable|string $slugGenerator): static;
}
