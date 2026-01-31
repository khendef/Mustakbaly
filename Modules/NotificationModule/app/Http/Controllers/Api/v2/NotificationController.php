<?php

namespace Modules\NotificationModule\Http\Controllers\Api\V2;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\NotificationModule\DTO\NotificationListDTO;
use Modules\NotificationModule\DTO\NotificationQueryDTO;
use Modules\NotificationModule\Services\NotificationService;

/**
 * API Controller for listing and updating notifications.
 * Business logic is handled by NotificationService.
 */
class NotificationController extends Controller
{
      public function __construct(private readonly NotificationService $service)
    {
    }

    /**
     * GET /api/notifications
     * Query params:
     * - page
     * - per_page
     * - unread_only (true/false)
     * - type (optional)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $dto = new NotificationQueryDTO(
            page: (int) $request->query('page', 1),
            perPage: (int) $request->query('per_page', 15),
            type: $request->query('type'),
            unreadOnly: filter_var($request->query('unread_only', false), FILTER_VALIDATE_BOOLEAN)
        );

        $paginator = $this->service->paginateForUser($user, $dto);

        return parent::paginated($paginator);
    }

    /**
     * PATCH /api/notifications/{id}/read
     */
    public function markRead(string $id)
    {
        $user = Auth::user();

        $notification = $this->service->markAsRead($user, $id);

        return parent::success($notification, 'Operation successful');
    }

    /**
     * PATCH /api/notifications/read-all
     */
    public function markAllRead()
    {
        $user = Auth::user();

        $updatedCount = $this->service->markAllAsRead($user);

        return parent::success(
            ['updated' => $updatedCount],
            'Operation successful'
        );
    }
}