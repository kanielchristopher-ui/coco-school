<?php
/**
 * TransportAssignment model.
 */
class TransportAssignment extends Model
{
    protected string $table = 'transport_assignments';

    public function listAll(): array
    {
        return $this->db->fetchAll(
            'SELECT ta.*, s.first_name, s.last_name, r.name AS route_name, v.vehicle_no
             FROM transport_assignments ta
             INNER JOIN students s ON s.id = ta.student_id
             LEFT JOIN routes r ON r.id = ta.route_id
             LEFT JOIN vehicles v ON v.id = ta.vehicle_id
             ORDER BY ta.id DESC'
        );
    }
}
