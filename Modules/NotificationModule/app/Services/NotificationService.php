<?php

namespace Modules\NotificationModule\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Collection;
use Modules\NotificationModule\DTO\NotificationDTO;
use Modules\NotificationModule\DTO\NotificationQueryDTO;
use Modules\UserManagementModule\Models\User;

/**
 * Encapsulates all notification business logic:
 * listing (sorting/filtering/pagination) and read operations.
 */
class NotificationService
{
    /**
     * Paginate notifications for the current authenticated user,
     * and transform each item into NotificationDTO array output.
     */
    public function paginateForUser($user, NotificationQueryDTO $query): LengthAwarePaginator
    {
        $builder = $user->notifications()->latest();

        if ($query->unreadOnly) {
            $builder->whereNull('read_at');
        }

        if ($query->type) {
            $builder->where('type', $query->type);
        }

        $paginator = $builder->paginate(
            perPage: $query->perPage,
            page: $query->page
        );

        $mapped = collect($paginator->items())
            ->map(fn (DatabaseNotification $n) => NotificationDTO::fromModel($n)->toArray());

        $paginator->setCollection(new Collection($mapped));

        return $paginator;
    }

    /**
     * Mark a single notification as read for the given user.
     */
    public function markAsRead($user, string $notificationId): array
    {
        $notification = $user->notifications()
            ->where('id', $notificationId)
            ->firstOrFail();

        $notification->markAsRead();

        return NotificationDTO::fromModel($notification)->toArray();
    }

    /**
     * Mark all notifications as read for the given user.
     */
    public function markAllAsRead($user): int
    {
        // Unread only
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
