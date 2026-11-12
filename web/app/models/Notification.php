<?php
/**
 * Notification model.
 */
class Notification extends Model
{
    protected string $table = 'notifications';

    /**
     * Latest notifications for a user.
     */
    public function latest(int $userId, int $limit = 5): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY id DESC LIMIT ' . (int) $limit,
            [$userId]
        );
    }

    /**
     * Count unread notifications for a user.
     */
    public function unreadCount(int $userId): int
    {
        return $this->db->count(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0',
            [$userId]
        );
    }

    /**
     * Create a notification for a user.
     */
    public function push(int $userId, string $title, string $message, string $type = 'info', ?string $link = null): void
    {
        $this->create([
            'user_id' => $userId,
            'title'   => $title,
            'message' => $message,
            'type'    => $type,
            'link'    => $link,
        ]);
    }
}
