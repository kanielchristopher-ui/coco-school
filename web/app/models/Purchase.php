<?php
/**
 * Purchase model.
 */
class Purchase extends Model
{
    protected string $table = 'purchases';

    public function listAll(): array
    {
        return $this->db->fetchAll(
            'SELECT pu.*, i.name AS item_name, sp.name AS supplier_name
             FROM purchases pu
             INNER JOIN inventory_items i ON i.id = pu.item_id
             LEFT JOIN suppliers sp ON sp.id = pu.supplier_id
             ORDER BY pu.id DESC'
        );
    }
}
