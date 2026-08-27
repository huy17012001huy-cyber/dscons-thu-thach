<?php

declare(strict_types=1);

namespace Modules\Learning\Application;

use App\Core\CommunityContext;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CourseCatalogManagementService
{
    public function __construct(private readonly CommunityContext $context) {}

    /** @param array<string, mixed> $attributes */
    public function save(?int $courseId, User $actor, array $attributes): ?Course
    {
        return DB::transaction(function () use ($courseId, $actor, $attributes): ?Course {
            $brand = $this->context->require();
            if (! $actor->isCommunityAdmin($brand->id)) {
                return null;
            }

            $course = $courseId === null ? null : Course::query()
                ->where('brand_id', $brand->id)
                ->whereKey($courseId)
                ->lockForUpdate()
                ->firstOrFail();
            if ($course) {
                $course->update($attributes);

                return $course->refresh();
            }

            return Course::create([...$attributes, 'brand_id' => $brand->id]);
        });
    }

    public function togglePublished(int $courseId, User $actor): ?Course
    {
        return DB::transaction(function () use ($courseId, $actor): ?Course {
            $course = $this->managedCourse($courseId, $actor);
            if (! $course) {
                return null;
            }

            $course->update(['is_published' => ! $course->is_published]);

            return $course->refresh();
        });
    }

    public function delete(int $courseId, User $actor): ?Course
    {
        return DB::transaction(function () use ($courseId, $actor): ?Course {
            $course = $this->managedCourse($courseId, $actor);
            if (! $course) {
                return null;
            }

            $course->delete();

            return $course;
        });
    }

    private function managedCourse(int $courseId, User $actor): ?Course
    {
        $brand = $this->context->require();
        if (! $actor->isCommunityAdmin($brand->id)) {
            return null;
        }

        return Course::query()
            ->where('brand_id', $brand->id)
            ->whereKey($courseId)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
