<?php
/**
 * StockIssue model.
 */
class StockIssue extends Model
{
    protected string $table = 'stock_issues';

    public function listAll(): array
    {
        return $this->db->fetchAll(
            'SELECT si.*, i.name AS item_name
             FROM stock_issues si
             INNER JOIN inventory_items i ON i.id = si.item_id
             ORDER BY si.id DESC'
        );
    }
}
