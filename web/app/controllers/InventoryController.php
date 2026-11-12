<?php
/**
 * ============================================================
 *  InventoryController
 *  Items, categories, suppliers, purchases and stock issues.
 * ============================================================
 */

declare(strict_types=1);

class InventoryController extends Controller
{
    public function index(): void
    {
        Auth::requirePermission('inventory.manage');

        $this->render('inventory/index', [
            'items'      => (new InventoryItem())->listAll(),
            'categories' => (new InventoryCategory())->all('name'),
            'suppliers'  => (new Supplier())->all('name'),
            'purchases'  => (new Purchase())->listAll(),
            'issues'     => (new StockIssue())->listAll(),
        ]);
    }

    public function storeCategory(): void
    {
        CSRF::check();
        Auth::requirePermission('inventory.manage');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Category name is required.');
            $this->redirect('inventory/index');
        }
        (new InventoryCategory())->create(['name' => $name]);
        Flash::set('success', 'Category added.');
        $this->redirect('inventory/index');
    }

    public function storeSupplier(): void
    {
        CSRF::check();
        Auth::requirePermission('inventory.manage');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Supplier name is required.');
            $this->redirect('inventory/index');
        }
        (new Supplier())->create([
            'name'           => $name,
            'contact_person' => trim($_POST['contact_person'] ?? ''),
            'phone'          => trim($_POST['phone'] ?? ''),
            'email'          => trim($_POST['email'] ?? ''),
            'address'        => trim($_POST['address'] ?? ''),
        ]);
        Flash::set('success', 'Supplier added.');
        $this->redirect('inventory/index');
    }

    public function storeItem(): void
    {
        CSRF::check();
        Auth::requirePermission('inventory.manage');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Item name is required.');
            $this->redirect('inventory/index');
        }

        $quantity = max(0, (int) ($_POST['quantity'] ?? 0));
        (new InventoryItem())->create([
            'name'          => $name,
            'category_id'   => (int) ($_POST['category_id'] ?? 0) ?: null,
            'supplier_id'   => (int) ($_POST['supplier_id'] ?? 0) ?: null,
            'quantity'      => $quantity,
            'unit_price'    => (float) ($_POST['unit_price'] ?? 0),
            'purchase_date' => trim($_POST['purchase_date'] ?? '') ?: null,
            'location'      => trim($_POST['location'] ?? ''),
            'status'        => $quantity === 0 ? 'out_of_stock' : ($quantity < 10 ? 'low_stock' : 'in_stock'),
        ]);

        Flash::set('success', 'Item added to inventory.');
        $this->redirect('inventory/index');
    }

    public function storePurchase(): void
    {
        CSRF::check();
        Auth::requirePermission('inventory.manage');

        $itemId = (int) ($_POST['item_id'] ?? 0);
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));
        $unitPrice = (float) ($_POST['unit_price'] ?? 0);

        if (!$itemId) {
            Flash::set('error', 'Select an item.');
            $this->redirect('inventory/index');
        }

        Database::getInstance()->insert('purchases', [
            'item_id'       => $itemId,
            'supplier_id'   => (int) ($_POST['supplier_id'] ?? 0) ?: null,
            'quantity'      => $qty,
            'unit_price'    => $unitPrice,
            'total'         => $qty * $unitPrice,
            'invoice_no'    => trim($_POST['invoice_no'] ?? ''),
            'purchase_date' => trim($_POST['purchase_date'] ?? '') ?: date('Y-m-d'),
        ]);

        // Increase stock.
        $item = (new InventoryItem())->find($itemId);
        if ($item) {
            $newQty = $item['quantity'] + $qty;
            (new InventoryItem())->update($itemId, [
                'quantity' => $newQty,
                'status'   => $newQty < 10 ? 'low_stock' : 'in_stock',
            ]);
        }

        Flash::set('success', 'Purchase recorded and stock updated.');
        $this->redirect('inventory/index');
    }

    public function storeIssue(): void
    {
        CSRF::check();
        Auth::requirePermission('inventory.manage');

        $itemId = (int) ($_POST['item_id'] ?? 0);
        $qty = max(1, (int) ($_POST['quantity'] ?? 1));

        if (!$itemId) {
            Flash::set('error', 'Select an item.');
            $this->redirect('inventory/index');
        }

        $item = (new InventoryItem())->find($itemId);
        if (!$item || $item['quantity'] < $qty) {
            Flash::set('error', 'Insufficient stock.');
            $this->redirect('inventory/index');
        }

        Database::getInstance()->insert('stock_issues', [
            'item_id'    => $itemId,
            'issued_to'  => trim($_POST['issued_to'] ?? ''),
            'quantity'   => $qty,
            'issue_date' => date('Y-m-d'),
            'remark'     => trim($_POST['remark'] ?? ''),
        ]);

        $newQty = $item['quantity'] - $qty;
        (new InventoryItem())->update($itemId, [
            'quantity' => $newQty,
            'status'   => $newQty === 0 ? 'out_of_stock' : ($newQty < 10 ? 'low_stock' : 'in_stock'),
        ]);

        Flash::set('success', 'Item issued.');
        $this->redirect('inventory/index');
    }

    public function destroyItem(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('inventory.manage');

        (new InventoryItem())->delete($id);
        Flash::set('success', 'Item deleted.');
        $this->redirect('inventory/index');
    }
}
