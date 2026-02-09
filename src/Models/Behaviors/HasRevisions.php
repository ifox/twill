<?php

namespace A17\Twill\Models\Behaviors;

use A17\Twill\Facades\TwillCapsules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

trait HasRevisions
{
    /**
     * Defines the one-to-many relationship for revisions.
     */
    public function revisions(): HasMany
    {
        return $this->hasMany($this->getRevisionModel())->orderBy('id', 'desc');
    }

    /**
     * Scope a query to only include the current user's revisions.
     */
    public function scopeMine(Builder $query): Builder
    {
        return $query->whereHas('revisions', function ($query) {
            $query->where('user_id', auth('twill_users')->user()->id);
        });
    }

    /**
     * Returns an array of revisions for the CMS views.
     */
    public function revisionsArray(): array
    {
        $currentRevision = null;

        $foreignKey = $this->revisions()->getForeignKeyName();

        $revisions = $this->revisions()
            ->select([$foreignKey, 'id', 'user_id', 'created_at', DB::raw("payload LIKE '%\"cmsSaveType\":\"draft-revision%' as is_draft")])
            ->get();

        return $revisions
            ->map(function ($revision) use (&$currentRevision) {
                if (! $currentRevision && ! $revision->is_draft) {
                    $currentRevision = $revision;
                }

                return [
                    'id' => $revision->id,
                    'author' => $revision->user->name ?? 'Unknown',
                    'datetime' => $revision->created_at->toIso8601String(),
                    'label' => $currentRevision === $revision ? twillTrans('twill::lang.publisher.current') : '',
                ];
            })
            ->toArray();
    }

    /**
     * Deletes revisions from specific collection position
     * Used to keep max revision on specific Twill's module.
     */
    public function deleteSpecificRevisions(int $maxRevisions): void
    {
        if (isset($this->limitRevisions) && $this->limitRevisions > 0) {
            $maxRevisions = $this->limitRevisions;
        }

        $this->revisions()->get()->slice($maxRevisions)->each->delete();
    }

    protected function getRevisionModel(): string
    {
        return TwillCapsules::guessRelatedModelClass('Revision', $this);
    }
}
