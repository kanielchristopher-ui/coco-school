<?php
/**
 * ============================================================
 *  FeeController
 *  Fee types, invoices, payments, discounts and receipts.
 * ============================================================
 */

declare(strict_types=1);

class FeeController extends Controller
{
    protected Fee $fee;
    protected Payment $payment;

    public function __construct()
    {
        parent::__construct();
        $this->fee = new Fee();
        $this->payment = new Payment();
    }

    public function index(): void
    {
        Auth::requirePermission('fees.manage');

        $search = trim($_GET['q'] ?? '');
        $status = trim($_GET['status'] ?? '');

        $rows = $this->fee->listAll($search ?: null, $status ?: null);
        $total = count($rows);
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $pagination = new Pagination($total, $page, PAGINATION_LIMIT);
        $rows = array_slice($rows, $pagination->offset(), PAGINATION_LIMIT);

        $this->render('fees/index', [
            'rows'       => $rows,
            'pagination' => $pagination,
            'total'      => $total,
            'search'     => $search,
            'status'     => $status,
            'feeTypes'   => (new FeeType())->all('name'),
            'students'   => Database::getInstance()->fetchAll('SELECT id, first_name, last_name, admission_no FROM students ORDER BY first_name'),
        ]);
    }

    /**
     * Generate an invoice for a student.
     */
    public function generateInvoice(): void
    {
        CSRF::check();
        Auth::requirePermission('fees.manage');

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $feeTypeId = (int) ($_POST['fee_type_id'] ?? 0);
        $amount    = (float) ($_POST['amount'] ?? 0);
        $dueDate   = trim($_POST['due_date'] ?? '');

        $feeType = $feeTypeId ? (new FeeType())->find($feeTypeId) : null;
        if (!$studentId || $amount <= 0) {
            Flash::set('error', 'Student and a valid amount are required.');
            $this->redirect('fees/index');
        }

        $this->fee->create([
            'invoice_no'      => $this->fee->nextInvoiceNo(),
            'student_id'      => $studentId,
            'fee_type_id'     => $feeTypeId ?: null,
            'amount'          => $amount,
            'discount_type'   => 'none',
            'discount_amount' => 0,
            'scholarship'     => 0,
            'due_date'        => $dueDate ?: date('Y-m-d', strtotime('+30 days')),
            'status'          => 'pending',
        ]);

        $this->logActivity('create', 'fees', 'Generated invoice for student #' . $studentId);
        Flash::set('success', 'Invoice generated successfully.');
        $this->redirect('fees/index');
    }

    /**
     * Show invoice detail with payment history.
     */
    public function show(int $id): void
    {
        Auth::requirePermission('fees.manage');

        $invoice = Database::getInstance()->fetch(
            'SELECT f.*, s.first_name, s.last_name, s.admission_no, ft.name AS fee_type_name, c.name AS class_name
             FROM fees f
             INNER JOIN students s ON s.id = f.student_id
             LEFT JOIN fee_types ft ON ft.id = f.fee_type_id
             LEFT JOIN classes c ON c.id = s.class_id
             WHERE f.id = ?',
            [$id]
        );

        if (!$invoice) {
            $this->renderError('Invoice not found.');
            return;
        }

        $paid = Database::getInstance()->fetchColumn('SELECT COALESCE(SUM(amount),0) FROM payments WHERE fee_id = ?', [$id]);
        $netAmount = $invoice['amount'] - $invoice['discount_amount'] - $invoice['scholarship'];
        $balance = max(0, $netAmount - (float) $paid);

        $this->render('fees/show', [
            'invoice'   => $invoice,
            'payments'  => $this->payment->forStudent($invoice['student_id']),
            'paid'      => (float) $paid,
            'balance'   => $balance,
            'netAmount' => $netAmount,
        ]);
    }

    /**
     * Record a payment against an invoice.
     */
    public function addPayment(int $feeId): void
    {
        CSRF::check();
        Auth::requirePermission('fees.manage');

        $invoice = $this->fee->find($feeId);
        if (!$invoice) {
            $this->renderError('Invoice not found.');
            return;
        }

        $amount = (float) ($_POST['amount'] ?? 0);
        if ($amount <= 0) {
            Flash::set('error', 'Payment amount must be greater than zero.');
            $this->redirect('fees/show/' . $feeId);
        }

        $paid = (float) Database::getInstance()->fetchColumn('SELECT COALESCE(SUM(amount),0) FROM payments WHERE fee_id = ?', [$feeId]);
        $net = $invoice['amount'] - $invoice['discount_amount'] - $invoice['scholarship'];

        $allowedMethods = ['cash', 'bank_transfer', 'cheque', 'card', 'online'];
        $method = trim($_POST['payment_method'] ?? 'cash');
        if (!in_array($method, $allowedMethods, true)) {
            $method = 'cash';
        }

        $paymentId = $this->payment->create([
            'receipt_no'     => $this->payment->nextReceiptNo(),
            'fee_id'         => $feeId,
            'student_id'     => $invoice['student_id'],
            'amount'         => $amount,
            'payment_method' => $method,
            'transaction_id' => trim($_POST['transaction_id'] ?? ''),
            'paid_by'        => $this->userId(),
            'payment_date'   => date('Y-m-d H:i:s'),
            'remark'         => trim($_POST['remark'] ?? ''),
        ]);

        // Update invoice status.
        $newPaid = $paid + $amount;
        $status = $newPaid >= $net ? 'paid' : ($newPaid > 0 ? 'partial' : 'pending');
        $this->fee->update($feeId, ['status' => $status]);

        $this->logActivity('payment', 'fees', 'Payment of ' . $amount . ' received on invoice #' . $feeId);
        Flash::set('success', 'Payment recorded successfully.');
        $this->redirect('fees/receipt/' . (int) $paymentId);
    }

    /**
     * Print a payment receipt.
     */
    public function receipt(int $id): void
    {
        Auth::requirePermission('fees.manage');

        $payment = Database::getInstance()->fetch(
            'SELECT p.*, f.invoice_no, f.fee_type_id, f.amount AS invoice_amount,
                    f.discount_amount, f.scholarship, ft.name AS fee_type_name,
                    s.first_name, s.last_name, s.admission_no, c.name AS class_name,
                    u.first_name AS cashier_first, u.last_name AS cashier_last
             FROM payments p
             INNER JOIN fees f ON f.id = p.fee_id
             INNER JOIN students s ON s.id = p.student_id
             LEFT JOIN fee_types ft ON ft.id = f.fee_type_id
             LEFT JOIN classes c ON c.id = s.class_id
             LEFT JOIN users u ON u.id = p.paid_by
             WHERE p.id = ?',
            [$id]
        );

        if (!$payment) {
            $this->renderError('Receipt not found.');
            return;
        }

        $netAmount = (float) $payment['invoice_amount'] - (float) $payment['discount_amount'] - (float) $payment['scholarship'];
        $paid = (float) Database::getInstance()->fetchColumn('SELECT COALESCE(SUM(amount),0) FROM payments WHERE fee_id = ?', [$payment['fee_id']]);
        $balance = max(0.0, $netAmount - $paid);

        $this->renderRaw('fees/receipt', [
            'payment'   => $payment,
            'netAmount' => $netAmount,
            'paidTotal' => $paid,
            'balance'   => $balance,
        ]);
    }

    /**
     * Receipt reprint search: find past payments by receipt / invoice / student.
     */
    public function receipts(): void
    {
        Auth::requirePermission('fees.manage');

        $q = trim($_GET['q'] ?? '');
        $rows = [];
        if ($q !== '') {
            $like = '%' . $q . '%';
            $rows = Database::getInstance()->fetchAll(
                'SELECT p.*, f.invoice_no, s.first_name, s.last_name, s.admission_no,
                        c.name AS class_name
                 FROM payments p
                 INNER JOIN fees f ON f.id = p.fee_id
                 INNER JOIN students s ON s.id = p.student_id
                 LEFT JOIN classes c ON c.id = s.class_id
                 WHERE p.receipt_no LIKE ? OR f.invoice_no LIKE ? OR s.admission_no LIKE ?
                    OR CONCAT(s.first_name, " ", s.last_name) LIKE ?
                 ORDER BY p.id DESC
                 LIMIT 100',
                [$like, $like, $like, $like]
            );
        }

        $this->render('fees/receipts', [
            'q'    => $q,
            'rows' => $rows,
        ]);
    }

    /**
     * Manage fee types.
     */
    public function types(): void
    {
        Auth::requirePermission('fees.manage');
        $this->render('fees/types', ['rows' => (new FeeType())->all('name')]);
    }

    public function storeType(): void
    {
        CSRF::check();
        Auth::requirePermission('fees.manage');

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Fee type name is required.');
            $this->redirect('fees/types');
        }

        (new FeeType())->create([
            'name'        => $name,
            'amount'      => (float) ($_POST['amount'] ?? 0),
            'frequency'   => trim($_POST['frequency'] ?? 'monthly'),
            'description' => trim($_POST['description'] ?? ''),
            'status'      => 1,
        ]);
        Flash::set('success', 'Fee type added.');
        $this->redirect('fees/types');
    }

    public function destroyType(int $id): void
    {
        CSRF::check();
        Auth::requirePermission('fees.manage');

        (new FeeType())->delete($id);
        Flash::set('success', 'Fee type deleted.');
        $this->redirect('fees/types');
    }

    protected function renderError(string $message): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
    }
}
