<?php
/**
 * Event model.
 */
class Event extends Model
{
    protected string $table = 'events';

    public function upcoming(int $limit = 5): array
    {
        return $this->db->fetchAll(
            'SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT ' . (int) $limit
        );
    }
}
