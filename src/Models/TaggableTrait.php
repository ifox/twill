<?php

namespace A17\Twill\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait TaggableTrait
{
    protected static string $tagsModel = Tag::class;

    protected $slugGenerator = 'Illuminate\Support\Str::slug';

    public function allTags(): Builder
    {
        return $this->getTagsModel()::query()->where('namespace', $this->getTagNamespace());
    }

    public function setTags(array|string $tags): void
    {
        $normalizedTags = $this->normalizeTags($tags);
        $currentTags = $this->tags()->pluck('name')->all();

        $this->untag(array_diff($currentTags, $normalizedTags));
        $this->tag($normalizedTags);
    }

    public function tag(array|string $tags): void
    {
        foreach ($this->buildTags($tags) as $tag) {
            if ($this->tags()->whereKey($tag->getKey())->exists()) {
                continue;
            }

            $this->tags()->attach($tag->getKey());
            $tag->increment('count');
        }
    }

    public function untag(array|string $tags): void
    {
        foreach ($this->buildTags($tags, createMissing: false) as $tag) {
            if (! $this->tags()->whereKey($tag->getKey())->exists()) {
                continue;
            }

            $this->tags()->detach($tag->getKey());

            if ($tag->count > 0) {
                $tag->decrement('count');
            }
        }
    }

    public function setSlugGenerator(callable|string $slugGenerator): static
    {
        $this->slugGenerator = $slugGenerator;

        return $this;
    }

    protected function getTagNamespace(): string
    {
        return $this->getMorphClass();
    }

    protected function getTagsModel(): string
    {
        return static::$tagsModel;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Tag>
     */
    protected function buildTags(array|string $tags, bool $createMissing = true): Collection
    {
        return collect($this->normalizeTags($tags))
            ->map(function (string $name) use ($createMissing) {
                $slug = $this->slugifyTag($name);

                if ($slug === '') {
                    return null;
                }

                $query = $this->getTagsModel()::query()->where([
                    'namespace' => $this->getTagNamespace(),
                    'slug' => $slug,
                ]);

                if (! $createMissing) {
                    return $query->first();
                }

                return $query->firstOrCreate([
                    'namespace' => $this->getTagNamespace(),
                    'slug' => $slug,
                ], [
                    'name' => $name,
                ]);
            })
            ->filter()
            ->values();
    }

    protected function normalizeTags(array|string $tags): array
    {
        $items = is_array($tags) ? $tags : explode(',', $tags);

        return collect($items)
            ->map(fn ($tag) => trim((string) $tag))
            ->filter(fn (string $tag) => $tag !== '')
            ->unique()
            ->values()
            ->all();
    }

    protected function slugifyTag(string $tag): string
    {
        $slugGenerator = $this->slugGenerator;

        if (is_string($slugGenerator) && Str::contains($slugGenerator, '::')) {
            return (string) forward_static_call($slugGenerator, $tag);
        }

        return (string) call_user_func($slugGenerator, $tag);
    }
}
