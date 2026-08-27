<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\Expedition;
use App\Models\ExpeditionMember;
use App\Models\User;
use App\Notifications\EventNotification;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class EventManagementService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function save(?int $eventId, User $actor, EventManagementData $data): Event
    {
        $brand = $this->context->require();
        $this->assertAdmin($actor, $brand->id);
        $this->assertReferences($data, $brand->id);

        return DB::transaction(function () use ($eventId, $actor, $data, $brand): Event {
            $event = $eventId === null
                ? new Event(['brand_id' => $brand->id, 'created_by' => $actor->id])
                : $this->lockedEvent($eventId, $brand->id);
            $wasPublished = $event->exists && $event->status === 'published';
            $scheduleChanged = $event->exists && (
                ! $event->starts_at
                || ! $event->starts_at->equalTo($data->startsAt)
                || ! $event->ends_at
                || ! $event->ends_at->equalTo($data->endsAt)
            );

            $event->fill([
                'course_id' => $data->courseId,
                'expedition_id' => $data->expeditionId,
                'title' => $data->title,
                'description' => $data->description,
                'event_type' => $data->eventType,
                'format' => $data->format,
                'starts_at' => $data->startsAt,
                'ends_at' => $data->endsAt,
                'meeting_url' => $data->meetingUrl,
                'location' => $data->location,
                'capacity' => $data->capacity,
                'status' => $data->status,
            ])->save();

            if ($event->status === 'published' && (! $wasPublished || $scheduleChanged)) {
                $prefix = $wasPublished ? 'Lịch sự kiện đã được cập nhật' : 'Sự kiện mới đã được công bố';
                DB::afterCommit(fn () => $this->notifyEligibleLearners($event, $prefix));
            }

            return $event;
        });
    }

    public function publish(int $eventId, User $actor): bool
    {
        return DB::transaction(function () use ($eventId, $actor): bool {
            $brand = $this->context->require();
            $this->assertAdmin($actor, $brand->id);
            $event = $this->lockedEvent($eventId, $brand->id);
            if ((! $event->course_id && ! $event->expedition_id) || $event->status === 'published') {
                return false;
            }

            $event->update(['status' => 'published']);
            DB::afterCommit(fn () => $this->notifyEligibleLearners($event, 'Sự kiện mới đã được công bố'));

            return true;
        });
    }

    public function updateStatus(int $eventId, User $actor, string $status): bool
    {
        if (! in_array($status, ['cancelled', 'completed'], true)) {
            throw new InvalidArgumentException('Unsupported event status.');
        }

        return DB::transaction(function () use ($eventId, $actor, $status): bool {
            $brand = $this->context->require();
            $this->assertAdmin($actor, $brand->id);
            $this->lockedEvent($eventId, $brand->id)->update(['status' => $status]);

            return true;
        });
    }

    public function delete(int $eventId, User $actor): bool
    {
        return DB::transaction(function () use ($eventId, $actor): bool {
            $brand = $this->context->require();
            $this->assertAdmin($actor, $brand->id);
            $event = $this->lockedEvent($eventId, $brand->id);
            if (! in_array($event->status, ['draft', 'cancelled'], true)) {
                return false;
            }

            $event->delete();

            return true;
        });
    }

    public function toggleAttendance(int $registrationId, User $actor): bool
    {
        return DB::transaction(function () use ($registrationId, $actor): bool {
            $brand = $this->context->require();
            $this->assertAdmin($actor, $brand->id);
            $registration = EventRegistration::query()
                ->where('brand_id', $brand->id)
                ->whereKey($registrationId)
                ->lockForUpdate()
                ->firstOrFail();
            $eventExists = Event::query()->where('brand_id', $brand->id)->whereKey($registration->event_id)->exists();
            if (! $eventExists) {
                return false;
            }

            $registration->update([
                'attended_at' => $registration->attended_at ? null : now(),
                'marked_attended_by' => $registration->attended_at ? null : $actor->id,
            ]);

            return true;
        });
    }

    private function assertReferences(EventManagementData $data, int $brandId): void
    {
        if (($data->courseId === null) === ($data->expeditionId === null)) {
            throw new InvalidArgumentException('Exactly one course or challenge is required.');
        }
        if ($data->courseId !== null && ! Course::query()->where('brand_id', $brandId)->whereKey($data->courseId)->exists()) {
            throw new InvalidArgumentException('Course does not belong to the current community.');
        }
        if ($data->expeditionId !== null && ! Expedition::query()->where('brand_id', $brandId)->whereKey($data->expeditionId)->exists()) {
            throw new InvalidArgumentException('Challenge does not belong to the current community.');
        }
    }

    private function assertAdmin(User $actor, int $brandId): void
    {
        if (! $actor->isCommunityAdmin($brandId)) {
            throw new AuthorizationException('Community admin access is required.');
        }
    }

    private function lockedEvent(int $eventId, int $brandId): Event
    {
        return Event::query()
            ->where('brand_id', $brandId)
            ->whereKey($eventId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function notifyEligibleLearners(Event $event, string $prefix): void
    {
        $brand = $this->context->require();
        $userIds = $event->course_id
            ? CourseEnrollment::query()->where('brand_id', $brand->id)->where('course_id', $event->course_id)->where('status', 'active')->pluck('user_id')
            : ExpeditionMember::query()->where('brand_id', $brand->id)->where('expedition_id', $event->expedition_id)->whereIn('status', ['approved', 'paid'])->whereNull('kicked_at')->pluck('user_id');

        User::query()->whereIn('id', $userIds->unique())->each(fn (User $user) => $user->notify(new EventNotification(
            $prefix.' — '.$brand->name,
            $prefix.': '.$event->title,
            community_route('events'),
        )));
    }
}
