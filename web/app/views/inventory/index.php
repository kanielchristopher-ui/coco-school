<?php if (!defined('APP_VERSION')) { exit; }
$pageTitle = 'Inventory Management';
?>
<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabItems" role="tab"><i class="fas fa-boxes me-1"></i>Items (<?= count($items) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPurchases" role="tab"><i class="fas fa-shopping-cart me-1"></i>Purchases (<?= count($purchases) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabIssues" role="tab"><i class="fas fa-box-open me-1"></i>Issues (<?= count($issues) ?>)</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabCategories" role="tab"><i class="fas fa-tags me-1"></i>Categories & Suppliers</a></li>
</ul>

<div class="tab-content">
    <!-- Items -->
    <div class="tab-pane fade show active" id="tabItems" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Item</th><th>Category</th><th>Supplier</th><th>Stock</th><th>Unit Price</th><th>Location</th><th>Status</th><th class="text-end">Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)): ?>
                                    <tr><td colspan="8" class="text-center text-muted py-4">No inventory items.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><strong><?= e($item['name']) ?></strong></td>
                                        <td><?= e($item['category_name'] ?: '—') ?></td>
                                        <td><?= e($item['supplier_name'] ?: '—') ?></td>
                                        <td>
                                            <?= (int) $item['quantity'] ?>
                                            <?php if ((int) $item['quantity'] === 0): ?>
                                                <span class="badge bg-danger ms-1">Out</span>
                                            <?php elseif ((int) $item['quantity'] < 10): ?>
                                                <span class="badge bg-warning text-dark ms-1">Low</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= currency($item['unit_price']) ?></td>
                                        <td><?= e($item['location'] ?: '—') ?></td>
                                        <td><?= statusBadge($item['status']) ?></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDelete('<?= url('inventory/destroy-item/' . $item['id']) ?>')"><i class="fas fa-trash"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-plus me-2"></i>Add Item</h6></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('inventory/store-item') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label required">Item Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label">Category</label>
                                    <select name="category_id" class="form-select">
                                        <option value="">Select</option>
                                        <?php foreach ($categories as $c): ?>
                                            <option value="<?= (int) $c['id'] ?>"><?= e($c['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Supplier</label>
                                    <select name="supplier_id" class="form-select">
                                        <option value="">Select</option>
                                        <?php foreach ($suppliers as $s): ?>
                                            <option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label required">Quantity</label>
                                    <input type="number" name="quantity" class="form-control" min="0" value="0" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">Unit Price</label>
                                    <input type="number" name="unit_price" class="form-control" min="0" step="0.01" value="0">
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" placeholder="e.g. Store Room 1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-control">
                            </div>
                            <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Add Item</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchases -->
    <div class="tab-pane fade" id="tabPurchases" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Item</th><th>Supplier</th><th>Invoice No</th><th>Qty</th><th>Unit Price</th><th>Total</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($purchases)): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">No purchases recorded.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($purchases as $pu): ?>
                                    <tr>
                                        <td><strong><?= e($pu['item_name']) ?></strong></td>
                                        <td><?= e($pu['supplier_name'] ?: '—') ?></td>
                                        <td><?= e($pu['invoice_no'] ?: '—') ?></td>
                                        <td><?= (int) $pu['quantity'] ?></td>
                                        <td><?= currency($pu['unit_price']) ?></td>
                                        <td class="fw-bold"><?= currency($pu['total']) ?></td>
                                        <td><?= formatDate($pu['purchase_date']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-cart-plus me-2"></i>Record Purchase</h6></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('inventory/store-purchase') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label required">Item</label>
                                <select name="item_id" class="form-select" required>
                                    <option value="">Select Item</option>
                                    <?php foreach ($items as $i): ?>
                                        <option value="<?= (int) $i['id'] ?>"><?= e($i['name']) ?> (<?= (int) $i['quantity'] ?> in stock)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Supplier</label>
                                <select name="supplier_id" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach ($suppliers as $s): ?>
                                        <option value="<?= (int) $s['id'] ?>"><?= e($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="form-label required">Quantity</label>
                                    <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label required">Unit Price</label>
                                    <input type="number" name="unit_price" class="form-control" min="0" step="0.01" required>
                                </div>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Invoice No</label>
                                <input type="text" name="invoice_no" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" name="purchase_date" class="form-control" value="<?= date('Y-m-d') ?>">
                            </div>
                            <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Record Purchase</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Issues -->
    <div class="tab-pane fade" id="tabIssues" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Item</th><th>Issued To</th><th>Qty</th><th>Date</th><th>Remark</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($issues)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">No stock issues recorded.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($issues as $iss): ?>
                                    <tr>
                                        <td><strong><?= e($iss['item_name']) ?></strong></td>
                                        <td><?= e($iss['issued_to'] ?: '—') ?></td>
                                        <td><?= (int) $iss['quantity'] ?></td>
                                        <td><?= formatDate($iss['issue_date']) ?></td>
                                        <td><?= e($iss['remark'] ?: '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-box-open me-2"></i>Issue Stock</h6></div>
                    <div class="card-body">
                        <form method="POST" action="<?= url('inventory/store-issue') ?>">
                            <?= csrf_field() ?>
                            <div class="mb-2">
                                <label class="form-label required">Item</label>
                                <select name="item_id" class="form-select" required>
                                    <option value="">Select Item</option>
                                    <?php foreach ($items as $i): ?>
                                        <option value="<?= (int) $i['id'] ?>"><?= e($i['name']) ?> (<?= (int) $i['quantity'] ?> in stock)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-2">
                                <label class="form-label required">Quantity</label>
                                <input type="number" name="quantity" class="form-control" min="1" value="1" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Issued To</label>
                                <input type="text" name="issued_to" class="form-control" placeholder="e.g. Mr. Smith">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Remark</label>
                                <textarea name="remark" class="form-control" rows="2"></textarea>
                            </div>
                            <button class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Issue Stock</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories & Suppliers -->
    <div class="tab-pane fade" id="tabCategories" role="tabpanel">
        <div class="row g-3">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Categories</h6>
                        <span class="badge bg-primary"><?= count($categories) ?></span>
                    </div>
                    <div class="card-body py-3">
                        <form method="POST" action="<?= url('inventory/store-category') ?>" class="d-flex gap-2">
                            <?= csrf_field() ?>
                            <input type="text" name="name" class="form-control" placeholder="Category name" required>
                            <button class="btn btn-primary text-nowrap"><i class="fas fa-plus"></i></button>
                        </form>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php if (empty($categories)): ?>
                            <div class="list-group-item text-muted text-center py-3">No categories.</div>
                        <?php endif; ?>
                        <?php foreach ($categories as $c): ?>
                            <div class="list-group-item"><?= e($c['name']) ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fas fa-truck me-2"></i>Suppliers</h6>
                        <span class="badge bg-primary"><?= count($suppliers) ?></span>
                    </div>
                    <div class="card-body py-3">
                        <form method="POST" action="<?= url('inventory/store-supplier') ?>" class="row g-2">
                            <?= csrf_field() ?>
                            <div class="col-md-4"><input type="text" name="name" class="form-control" placeholder="Supplier name" required></div>
                            <div class="col-md-4"><input type="text" name="contact_person" class="form-control" placeholder="Contact person"></div>
                            <div class="col-md-2"><input type="text" name="phone" class="form-control" placeholder="Phone"></div>
                            <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fas fa-plus"></i></button></div>
                            <div class="col-md-6"><input type="email" name="email" class="form-control" placeholder="Email"></div>
                            <div class="col-md-6"><input type="text" name="address" class="form-control" placeholder="Address"></div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>Name</th><th>Contact</th><th>Phone</th><th>Email</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($suppliers)): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-3">No suppliers.</td></tr>
                                <?php endif; ?>
                                <?php foreach ($suppliers as $s): ?>
                                    <tr>
                                        <td><strong><?= e($s['name']) ?></strong></td>
                                        <td><?= e($s['contact_person'] ?: '—') ?></td>
                                        <td><?= e($s['phone'] ?: '—') ?></td>
                                        <td><?= e($s['email'] ?: '—') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
