<?php
/**
 * InventoryItem model.
 */
class InventoryItem extends Model
{
    protected string $table = 'inventory_items';

    public function listAll(): array
    {
        return $this->db->fetchAll(
            'SELECT i.*, ic.name AS category_name, sp.name AS supplier_name
             FROM inventory_items i
             LEFT JOIN inventory_categories ic ON ic.id = i.category_id
             LEFT JOIN suppliers sp ON sp.id = i.supplier_id
             ORDER BY i.name ASC'
        );
    }
}
