<?php

declare(strict_types=1);

namespace Modules\Community\Application;

use App\Core\CommunityContext;
use App\Models\CourseEnrollment;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\ExpeditionMember;
use App\Models\User;
use App\Notifications\EventNotification;
use Illuminate\Support\Facades\DB;

final class EventRegistrationService
{
    public function __construct(private readonly CommunityContext $context) {}

    public function register(int $eventId, User $user): EventRegistrationOutcome
    {
        return DB::transaction(function () use ($eventId, $user): EventRegistrationOutcome {
            $brand = $this->context->require();
            $event = Event::query()
                ->where('brand_id', $brand->id)
                ->whereKey($eventId)
                ->lockForUpdate()
                ->firstOrFail();
            if (! $this->isEligible($event, $user)) {
                return EventRegistrationOutcome::NotEligible;
            }
            if ($event->status !== 'published' || $event->ends_at?->isPast()) {
                return EventRegistrationOutcome::Closed;
            }

            $registration = EventRegistration::query()
                ->where('brand_id', $brand->id)
                ->where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();
            if ($registration?->status === 'registered') {
                return EventRegistrationOutcome::AlreadyRegistered;
            }

            $registeredCount = EventRegistration::query()
                ->where('brand_id', $brand->id)
                ->where('event_id', $event->id)
                ->where('status', 'registered')
                ->count();
            if ($event->capacity !== null && $registeredCount >= $event->capacity) {
                return EventRegistrationOutcome::Full;
            }

            if ($registration) {
                $registration->update(['status' => 'registered', 'registered_at' => now()]);
            } else {
                EventRegistration::create([
                    'brand_id' => $brand->id,
                    'event_id' => $event->id,
                    'user_id' => $user->id,
                    'status' => 'registered',
                    'registered_at' => now(),
                ]);
            }

            DB::afterCommit(fn () => $this->sendRegistrationNotification($user, $event));

            return EventRegistrationOutcome::Registered;
        });
    }

    public function cancel(int $eventId, User $user): bool
    {
        return DB::transaction(function () use ($eventId, $user): bool {
            $brand = $this->context->require();
            $registration = EventRegistration::query()
                ->where('brand_id', $brand->id)
                ->where('event_id', $eventId)
                ->where('user_id', $user->id)
                ->where('status', 'registered')
                ->lockForUpdate()
                ->first();
            if (! $registration) {
                return false;
            }

            $eventExists = Event::query()->where('brand_id', $brand->id)->whereKey($eventId)->exists();
            if (! $eventExists) {
                return false;
            }

            $registration->update(['status' => 'cancelled']);

            return true;
        });
    }

    public function isEligible(Event $event, User $user): bool
    {
        $brand = $this->context->require();
        if ($event->brand_id !== $brand->id) {
            return false;
        }
        if ($user->isCommunityAdmin($brand->id) || $user->hasPremiumMembership($brand->id)) {
            return true;
        }

        if ($event->course_id) {
            return CourseEnrollment::query()
                ->where('brand_id', $brand->id)
                ->where('course_id', $event->course_id)
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();
        }

        if ($event->expedition_id) {
            return ExpeditionMember::query()
                ->where('brand_id', $brand->id)
                ->where('expedition_id', $event->expedition_id)
                ->where('user_id', $user->id)
                ->whereIn('status', ['approved', 'paid'])
                ->whereNull('kicked_at')
                ->exists();
        }

        return false;
    }

    private function sendRegistrationNotification(User $user, Event $event): void
    {
        $brand = $this->context->require();
        $user->notify(new EventNotification(
            'Đăng ký sự kiện — '.$brand->name,
            'Bạn đã đăng ký sự kiện “'.$event->title.'”.',
            community_route('events'),
        ));
    }
}
