<?php
/**
 * Vehicle model.
 */
class Vehicle extends Model
{
    protected string $table = 'vehicles';

    public function listAll(): array
    {
        return $this->db->fetchAll(
            'SELECT v.*, d.name AS driver_name
             FROM vehicles v
             LEFT JOIN drivers d ON d.id = v.driver_id
             ORDER BY v.vehicle_no ASC'
        );
    }
}
