<?php

namespace Modules\NotificationModule\DTO;

use Illuminate\Notifications\DatabaseNotification;

/**
 * Class NotificationDTO
 *
 * Data Transfer Object (DTO) used to standardize the shape of notifications
 * returned by the Notifications API.
 *
 * The source model is usually Illuminate\Notifications\DatabaseNotification
 * which stores the payload in the "data" column (JSON) in the "notifications" table.
 *
 * Typical stored formats in $notification->data:
 * 1) Plain strings:
 *    [
 *      'title' => 'Quiz Attempt Graded',
 *      'body'  => 'Your score: 90',
 *      ...
 *    ]
 *
 * 2) Translated arrays:
 *    [
 *      'title' => ['ar' => 'تم تصحيح الاختبار', 'en' => 'Quiz graded'],
 *      'body'  => ['ar' => 'درجتك: 90', 'en' => 'Your score: 90'],
 *      ...
 *    ]
 *
 * This DTO normalizes both formats so the API always returns:
 * - title: { ar: "...", en: "..." }
 * - body:  { ar: "...", en: "..." }
 */
class NotificationDTO
{
    /**
     * Create a new NotificationDTO instance.
     *
     * @param string $id        The notification UUID from the notifications table.
     * @param string $type      Fully-qualified notification class name (e.g. Modules\...\QuizAttemptGraded).
     * @param array  $data      Full notification payload stored in the "data" column.
     * @param array  $title     Normalized translatable title array: ['ar' => ..., 'en' => ...]
     * @param array  $body      Normalized translatable body array:  ['ar' => ..., 'en' => ...]
     * @param bool   $isRead    Whether notification has been read (read_at not null).
     * @param ?string $readAt   ISO-8601 read timestamp or null if unread.
     * @param string $createdAt ISO-8601 created timestamp.
     */
    public function __construct(
        public string $id,
        public string $type,
        public array $data,
        public array $title,
        public array $body,
        public bool $isRead,
        public ?string $readAt,
        public string $createdAt
    ) {}

    /**
     * Build a NotificationDTO from a DatabaseNotification model.
     *
     * Steps:
     * 1) Read $notification->data (already casted to array by Laravel in most cases).
     * 2) Extract title/body from the payload.
     * 3) Normalize title/body so they always become translated arrays.
     *
     * @param DatabaseNotification $n The database notification model.
     * @return self
     */
    public static function fromModel(DatabaseNotification $n): self
    {
        $data = is_array($n->data) ? $n->data : (array) $n->data;

        $title = self::normalizeTranslatable($data['title'] ?? '');
        $body  = self::normalizeTranslatable($data['body'] ?? '');

        return new self(
            id: (string) $n->id,
            type: (string) $n->type,
            data: $data,
            title: $title,
            body: $body,
            isRead: $n->read_at !== null,
            readAt: $n->read_at?->toISOString(),
            createdAt: $n->created_at?->toISOString() ?? now()->toISOString()
        );
    }

    /**
     * Normalize a value to a translatable array format:
     *
     * Input can be:
     * - array: ['ar' => '...', 'en' => '...'] (or missing one of them)
     * - string/int/etc: '...' -> converts to ['ar' => '...', 'en' => '...']
     *
     * Rules:
     * - If an array is provided and 'ar' is missing, fallback to 'en'.
     * - If an array is provided and 'en' is missing, fallback to 'ar'.
     * - If scalar value is provided, same text is used for both languages.
     *
     * @param mixed $value Title/body value from notification payload.
     * @return array{ar:string,en:string}
     */
    private static function normalizeTranslatable(mixed $value): array
    {


    if (is_array($value)) {
            return [
                'ar' => (string) ($value['ar'] ?? ($value['en'] ?? '')),
                'en' => (string) ($value['en'] ?? ($value['ar'] ?? '')),
            ];
        }

        $text = (string) $value;

        return [
            'ar' => $text,
            'en' => $text,
        ];
    }

    /**
     * Convert DTO to an array ready for JSON responses.
     *
     * Output shape example:
     * [
     *   'id' => '...',
     *   'type' => 'Modules\...\QuizAttemptGraded',
     *   'title' => ['ar' => '...', 'en' => '...'],
     *   'body'  => ['ar' => '...', 'en' => '...'],
     *   'data' => [...],
     *   'is_read' => true/false,
     *   'read_at' => '2026-01-30T11:00:00Z' or null,
     *   'created_at' => '2026-01-30T10:00:00Z',
     * ]
     *
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'title'      => $this->title,
            'body'       => $this->body,
            'data'       => $this->data,
            'is_read'    => $this->isRead,
            'read_at'    => $this->readAt,
            'created_at' => $this->createdAt,
        ];
    }
}