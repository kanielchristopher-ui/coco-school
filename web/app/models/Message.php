<?php
/**
 * Message model.
 */
class Message extends Model
{
    protected string $table = 'messages';

    /**
     * Conversations between two users.
     */
    public function conversation(int $user1, int $user2): array
    {
        return $this->db->fetchAll(
            'SELECT m.*, u.first_name AS sender_first, u.last_name AS sender_last
             FROM messages m
             INNER JOIN users u ON u.id = m.sender_id
             WHERE (m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?)
             ORDER BY m.id ASC',
            [$user1, $user2, $user2, $user1]
        );
    }

    /**
     * Messages received by a user.
     */
    public function inbox(int $userId): array
    {
        return $this->db->fetchAll(
            'SELECT m.*, u.first_name AS sender_first, u.last_name AS sender_last
             FROM messages m
             INNER JOIN users u ON u.id = m.sender_id
             WHERE m.receiver_id = ?
             ORDER BY m.id DESC',
            [$userId]
        );
    }

    /**
     * Count unread messages for a user.
     */
    public function unreadCount(int $userId): int
    {
        return $this->db->count(
            'SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0',
            [$userId]
        );
    }
}
