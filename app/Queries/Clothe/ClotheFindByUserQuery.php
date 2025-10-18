<?php

namespace App\Queries\Clothe;

use App\Models\Clothe\Clothe;
use Illuminate\Database\Eloquent\Collection;

final class ClotheFindByUserQuery
{
    public function query(int $userId, ?array $excludeIds = null): Collection
    {
        $query = Clothe::query()
            ->ownedBy($userId)
            ->whereNull('deleted_at');

        if ($excludeIds && !empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->get();
    }

    public function queryByIds(int $userId, array $clotheIds): Collection
    {
        return Clothe::query()
            ->ownedBy($userId)
            ->whereIn('id', $clotheIds)
            ->whereNull('deleted_at')
            ->get();
    }

    public function queryByCategory(int $userId, string $category, ?array $excludeIds = null): Collection
    {
        $query = Clothe::query()
            ->ownedBy($userId)
            ->where('category', $category)
            ->whereNull('deleted_at');

        if ($excludeIds && !empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->get();
    }

    public function queryByOccasion(int $userId, string $occasion, ?array $excludeIds = null): Collection
    {
        $query = Clothe::query()
            ->ownedBy($userId)
            ->where('occasion', $occasion)
            ->whereNull('deleted_at');

        if ($excludeIds && !empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        return $query->get();
    }
}
