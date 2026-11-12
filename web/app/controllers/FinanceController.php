<?php
/**
 * ============================================================
 *  FinanceController
 *  School finance module: student fees, fee
 *  collection, payment receipts, staff payroll, school
 *  expenses, expense categories and finance reports.
 *  Addition only - existing modules are not touched.
 * ============================================================
 */

declare(strict_types=1);

class FinanceController extends Controller
{
    protected const FEE_TYPES = [
        'Registration Fee',
        'Monthly Tuition',
        'Exam Fee',
        'Transport Fee',
        'Report Card & ID Fee',
        'School Uniform',
        'Other',
    ];

    protected const PAYMENT_METHODS = [
        'cash'          => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'cheque'        => 'Cheque',
        'mobile_money'  => 'Mobile Money',
        'card'          => 'Card',
        'online'        => 'Online',
    ];

    protected StudentFee $studentFee;
    protected FeeCollection $feeCollection;
    protected PayrollRecord $payroll;
    protected ExpenseCategory $expenseCategory;
    protected Expense $expense;

    public function __construct()
    {
        parent::__construct();
        $this->studentFee     = new StudentFee();
        $this->feeCollection  = new FeeCollection();
        $this->payroll        = new PayrollRecord();
        $this->expenseCategory = new ExpenseCategory();
        $this->expense        = new Expense();
    }

    /**
     * Landing redirect (keeps the System Dashboard untouched).
     */
    public function index(): void
    {
        $this->authorize();
        $this->redirect('finance/fees');
    }

    /**
     * Restrict the whole module to management roles.
     */
    protected function authorize(): void
    {
        Auth::requireRole(['admin', 'principal', 'prefet', 'director', 'cashier']);
    }

    protected function renderError(string $message): void
    {
        http_response_code(404);
        $this->render('errors/404', ['message' => $message]);
    }

    /**
     * All students for dropdowns (id, names, admission no, class id).
     */
    protected function allStudents(): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT id, first_name, last_name, admission_no, class_id
             FROM students ORDER BY first_name'
        );
    }

    protected function allSessions(): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT id, name FROM academic_sessions ORDER BY name DESC'
        );
    }

    protected function monthsList(): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $months[] = date('Y-m', strtotime('-' . $i . ' months'));
        }
        return $months;
    }

    /**
     * Student fees that may still be paid today: status pending or partial
     * with an outstanding balance (amount minus active payments). Payments
     * are matched to a fee the same way syncStatusAfterPayment() does it:
     * same student, same fee type and, for monthly fees, the same billing
     * month as the payment date.
     */
    protected function availableFees(): array
    {
        $rows = Database::getInstance()->fetchAll(
            "SELECT sf.id, sf.student_id, sf.class_id, sf.fee_type, sf.amount,
                    sf.academic_session, sf.month, sf.status,
                    s.first_name, s.last_name, s.admission_no, c.name AS class_name,
                    COALESCE(SUM(p.total_paid), 0) AS total_paid
             FROM student_fees sf
             INNER JOIN students s ON s.id = sf.student_id
             LEFT JOIN classes c ON c.id = sf.class_id
             LEFT JOIN (
                 SELECT fc.student_id, fc.fee_type, DATE_FORMAT(fc.payment_date, '%Y-%m') AS pay_month,
                        SUM(fc.amount_paid) AS total_paid
                 FROM fee_collections fc
                 WHERE fc.status = 'active'
                 GROUP BY fc.student_id, fc.fee_type, DATE_FORMAT(fc.payment_date, '%Y-%m')
             ) p ON p.student_id = sf.student_id AND p.fee_type = sf.fee_type
                AND (sf.month IS NULL OR p.pay_month = sf.month)
             WHERE sf.status IN ('pending', 'partial')
             GROUP BY sf.id, sf.student_id, sf.class_id, sf.fee_type, sf.amount,
                      sf.academic_session, sf.month, sf.status,
                      s.first_name, s.last_name, s.admission_no, c.name
             HAVING (sf.amount - COALESCE(SUM(p.total_paid), 0)) > 0
             ORDER BY s.first_name, sf.fee_type, sf.id"
        );

        foreach ($rows as &$fee) {
            $fee['total_paid'] = (float) $fee['total_paid'];
            $fee['remaining']  = max(0.0, (float) $fee['amount'] - $fee['total_paid']);
        }
        unset($fee);

        return $rows;
    }

    /**
     * A single student fee with its paid amount and remaining balance, or
     * null when the fee does not exist.
     */
    protected function feeWithRemaining(int $feeId): ?array
    {
        $fee = $this->studentFee->find($feeId);
        if (!$fee) {
            return null;
        }

        $totalPaid = (float) Database::getInstance()->fetchColumn(
            "SELECT COALESCE(SUM(fc.amount_paid), 0)
             FROM fee_collections fc
             WHERE fc.student_id = ? AND fc.fee_type = ?
               AND fc.status = 'active'
               AND (? IS NULL OR DATE_FORMAT(fc.payment_date, '%Y-%m') = ?)",
            [(int) $fee['student_id'], $fee['fee_type'], $fee['month'] ?: null, $fee['month'] ?: null]
        );

        $fee['total_paid'] = $totalPaid;
        $fee['remaining']  = max(0.0, (float) $fee['amount'] - $totalPaid);
        return $fee;
    }

    /**
     * Fee Collection payment records connected to a Student Fee record.
     * The same matching rule as syncStatusAfterPayment() is used: same
     * student, same fee type and, for monthly fees, the same billing month
     * as the payment date.
     */
    protected function relatedPayments(array $fee): array
    {
        return Database::getInstance()->fetchAll(
            'SELECT fc.id, fc.student_id, fc.receipt_no
             FROM fee_collections fc
             WHERE fc.student_id = ? AND fc.fee_type = ?
               AND (? IS NULL OR DATE_FORMAT(fc.payment_date, "%Y-%m") = ?)
             ORDER BY fc.id',
            [(int) $fee['student_id'], $fee['fee_type'], $fee['month'] ?: null, $fee['month'] ?: null]
        );
    }

    // ============================================================
    //  1. STUDENT FEES
    // ============================================================

    public function fees(): void
    {
        $this->authorize();

        $status  = trim($_GET['status'] ?? '');
        $classId = (int) ($_GET['class_id'] ?? 0);

        $rows  = $this->studentFee->listAll($status ?: null, $classId ?: null);
        $total = count($rows);
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $pagination = new Pagination($total, $page, PAGINATION_LIMIT);
        $rows = array_slice($rows, $pagination->offset(), PAGINATION_LIMIT);

        // Flag fees that have related payment records so the delete action can
        // warn about the cascade before asking for confirmation.
        foreach ($rows as &$r) {
            $r['has_related'] = !empty($this->relatedPayments($r));
        }
        unset($r);

        $this->render('finance/fees', [
            'rows'       => $rows,
            'pagination' => $pagination,
            'status'     => $status,
            'classId'    => $classId,
            'feeTypes'   => self::FEE_TYPES,
            'classes'    => (new ClassRoom())->all('name'),
            'students'   => $this->allStudents(),
            'sessions'   => $this->allSessions(),
            'summary'    => [
                'paid'    => $this->studentFee->totalAmount('paid'),
                'partial' => $this->studentFee->totalAmount('partial'),
                'pending' => $this->studentFee->totalAmount('pending'),
            ],
        ]);
    }

    public function feesStore(): void
    {
        CSRF::check();
        $this->authorize();

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $feeType   = trim($_POST['fee_type'] ?? '');
        $amount    = (float) ($_POST['amount'] ?? 0);
        $session   = trim($_POST['academic_session'] ?? '');
        $month     = trim($_POST['month'] ?? '');
        $dueDate   = trim($_POST['due_date'] ?? '');
        $status    = in_array(trim($_POST['status'] ?? ''), ['pending', 'partial', 'paid'], true)
                    ? $_POST['status'] : 'pending';

        $student = $studentId ? (new Student())->find($studentId) : null;
        if (!$student || $amount <= 0 || $feeType === '' || $session === '') {
            Flash::set('error', 'Student, fee type, amount and academic session are required.');
            $this->redirect('finance/fees');
        }
        if (!in_array($feeType, self::FEE_TYPES, true)) {
            Flash::set('error', 'Invalid fee type.');
            $this->redirect('finance/fees');
        }

        $this->studentFee->create([
            'student_id'       => $studentId,
            'class_id'         => (int) $student['class_id'] ?: null,
            'fee_type'         => $feeType,
            'amount'           => $amount,
            'academic_session' => $session,
            'month'            => $month ?: null,
            'due_date'         => $dueDate ?: null,
            'status'           => $status,
            'created_by'       => $this->userId(),
        ]);

        $this->logActivity('create', 'finance', 'Added student fee ' . $feeType . ' for student #' . $studentId);
        Flash::set('success', 'Student fee added.');
        $this->redirect('finance/fees');
    }

    public function feesDelete(int $id): void
    {
        CSRF::check();
        $this->authorize();

        $fee = $this->studentFee->find($id);
        if (!$fee) {
            Flash::set('error', 'Student fee not found.');
            $this->redirect('finance/fees');
            return;
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $payments = $this->relatedPayments($fee);
            $deletedPayments = 0;

            if (!empty($payments)) {
                // Remove the payment notifications sent to the student for
                // these exact payments (link points to each receipt).
                $student = (new Student())->find((int) $fee['student_id']);
                $studentUserId = (int) ($student['user_id'] ?? 0);
                if ($studentUserId > 0) {
                    $links = array_map(static fn (array $p): string => 'finance/my-receipt/' . (int) $p['id'], $payments);
                    $placeholders = implode(',', array_fill(0, count($links), '?'));
                    $db->delete(
                        'notifications',
                        'user_id = ? AND link IN (' . $placeholders . ')',
                        array_merge([$studentUserId], $links)
                    );
                }

                // Remove the related payment records. Receipts are rendered
                // from these rows, so they are removed together with them.
                $ids = array_map(static fn (array $p): int => (int) $p['id'], $payments);
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $deletedPayments = $db->delete('fee_collections', 'id IN (' . $placeholders . ')', $ids);
            }

            // Remove the Student Fee record itself.
            $this->studentFee->delete($id);

            $db->commit();

            $this->logActivity('delete', 'finance', 'Deleted student fee #' . $id . ' with ' . $deletedPayments . ' related payment(s)');
            Flash::set('success', $deletedPayments > 0
                ? 'Student fee deleted. Related payments, receipts and notifications were removed.'
                : 'Student fee deleted.');
            $this->redirect('finance/fees');
        } catch (Throwable $e) {
            $db->rollBack();
            Flash::set('error', 'Could not delete the student fee. Please try again.');
            $this->redirect('finance/fees');
        }
    }

    // ============================================================
    //  2. FEE COLLECTION
    // ============================================================

    public function collections(): void
    {
        $this->authorize();

        $classId = (int) ($_GET['class_id'] ?? 0);

        $rows  = $this->feeCollection->listAll($classId ?: null);
        $total = count($rows);
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $pagination = new Pagination($total, $page, PAGINATION_LIMIT);
        $rows = array_slice($rows, $pagination->offset(), PAGINATION_LIMIT);

        $this->render('finance/collections', [
            'rows'           => $rows,
            'pagination'     => $pagination,
            'classId'        => $classId,
            'feeTypes'       => self::FEE_TYPES,
            'methods'        => self::PAYMENT_METHODS,
            'classes'        => (new ClassRoom())->all('name'),
            'students'       => $this->allStudents(),
            'availableFees'  => $this->availableFees(),
            'voidedCount'    => $this->voidedCount(),
            'totalCollected' => $this->feeCollection->totalCollected(),
        ]);
    }

    public function collectionsStore(): void
    {
        CSRF::check();
        $this->authorize();

        $studentId   = (int) ($_POST['student_id'] ?? 0);
        $feeType     = trim($_POST['fee_type'] ?? '');
        $amount      = (float) ($_POST['amount_paid'] ?? 0);
        $paymentDate = trim($_POST['payment_date'] ?? date('Y-m-d'));
        $method      = trim($_POST['payment_method'] ?? 'cash');
        $notes       = trim($_POST['notes'] ?? '');
        $feeId       = (int) ($_POST['fee_id'] ?? 0);

        $student = $studentId ? (new Student())->find($studentId) : null;
        if (!$student || $amount <= 0 || $feeType === '') {
            Flash::set('error', 'Student, fee type and a valid amount are required.');
            $this->redirect('finance/collections');
        }
        if (!in_array($feeType, self::FEE_TYPES, true)) {
            Flash::set('error', 'Invalid fee type.');
            $this->redirect('finance/collections');
        }
        if (!array_key_exists($method, self::PAYMENT_METHODS)) {
            $method = 'cash';
        }

        // When an outstanding fee was selected from the dropdown, the payment
        // is locked to that fee: it must exist, belong to the chosen student
        // and still be pending or partially paid, and the amount may not
        // exceed the remaining balance (no duplicate / over payment).
        if ($feeId > 0) {
            $fee = $this->feeWithRemaining($feeId);
            if (!$fee || (int) $fee['student_id'] !== $studentId) {
                Flash::set('error', 'The selected fee is not valid for this student.');
                $this->redirect('finance/collections');
            }
            if (!in_array($fee['status'], ['pending', 'partial'], true)) {
                Flash::set('error', 'This fee is already fully paid and cannot accept another payment.');
                $this->redirect('finance/collections');
            }
            if ($amount > $fee['remaining'] + 0.001) {
                Flash::set('error', 'The amount exceeds the outstanding balance of ' . currency($fee['remaining']) . '.');
                $this->redirect('finance/collections');
            }
            // The fee type always comes from the selected fee record.
            $feeType = $fee['fee_type'];
        } else {
            // Manual entry (no fee selected): a payment is only allowed when a
            // matching Student Fee record exists for this student and fee type.
            // The same matching rule as syncStatusAfterPayment() is used: for
            // monthly fees the payment month must equal the fee's billing month.
            $matching = Database::getInstance()->fetchAll(
                'SELECT f.id, f.fee_type, f.month, f.academic_session, f.status
                 FROM student_fees f
                 WHERE f.student_id = ? AND f.fee_type = ?
                   AND (f.month IS NULL OR DATE_FORMAT(?, "%Y-%m") = f.month)',
                [$studentId, $feeType, $paymentDate ?: date('Y-m-d')]
            );
            if (empty($matching)) {
                Flash::set('error', 'No Student Fee record found for this student. Please create Student Fee first.');
                $this->redirect('finance/collections');
            }
            $hasOpen = false;
            foreach ($matching as $m) {
                if ($m['status'] === 'pending' || $m['status'] === 'partial') {
                    $hasOpen = true;
                    break;
                }
            }
            if (!$hasOpen) {
                Flash::set('error', 'This fee is already fully paid. Please select the outstanding fee from the list.');
                $this->redirect('finance/collections');
            }
        }

        $receiptNo = $this->feeCollection->nextReceiptNo();
        $id = $this->feeCollection->create([
            'student_id'     => $studentId,
            'class_id'       => (int) $student['class_id'] ?: null,
            'fee_type'       => $feeType,
            'amount_paid'    => $amount,
            'payment_date'   => $paymentDate ?: date('Y-m-d'),
            'payment_method' => $method,
            'collected_by'   => $this->userId(),
            'notes'          => $notes ?: null,
            'receipt_no'     => $receiptNo,
        ]);

        // Auto-sync the related Student Fee status (paid / partial / pending)
        // from the accumulated payments for that fee.
        $this->studentFee->syncStatusAfterPayment($studentId, $feeType, $paymentDate ?: date('Y-m-d'));

        // Notify only the student who made this payment (linked user account).
        $studentUserId = (int) ($student['user_id'] ?? 0);
        if ($studentUserId > 0) {
            (new Notification())->push(
                $studentUserId,
                'Payment Received Successfully',
                'Your payment of ' . currency($amount) . ' for ' . $feeType
                    . ' on ' . formatDate($paymentDate ?: date('Y-m-d'))
                    . ' has been received successfully. Receipt No: ' . $receiptNo
                    . '. Your receipt is now available.',
                'success',
                'finance/my-receipt/' . (int) $id
            );
        }

        $this->logActivity('create', 'finance', 'Collected ' . $amount . ' from student #' . $studentId . ' (' . $feeType . ')');
        Flash::set('success', 'Payment recorded and receipt generated.');
        $this->redirect('finance/receipt/' . (int) $id);
    }

    /**
     * Edit an existing payment (amount, fee type, date, method, notes).
     * The same receipt is kept and updated automatically - no duplicate
     * receipt is created. The related Student Fee status is re-synced for
     * both the previous and the new fee, and the student is notified.
     */
    public function collectionsUpdate(int $id): void
    {
        CSRF::check();
        $this->authorize();

        $collection = $this->feeCollection->find($id);
        if (!$collection) {
            Flash::set('error', 'Payment not found.');
            $this->redirect('finance/collections');
            return;
        }
        if (($collection['status'] ?? 'active') === 'voided') {
            Flash::set('error', 'A voided payment cannot be edited.');
            $this->redirect('finance/collections');
            return;
        }

        $feeType     = trim($_POST['fee_type'] ?? '');
        $amount      = (float) ($_POST['amount_paid'] ?? 0);
        $paymentDate = trim($_POST['payment_date'] ?? date('Y-m-d'));
        $method      = trim($_POST['payment_method'] ?? 'cash');
        $notes       = trim($_POST['notes'] ?? '');

        if ($amount <= 0 || $feeType === '') {
            Flash::set('error', 'Fee type and a valid amount are required.');
            $this->redirect('finance/collections');
            return;
        }
        if (!in_array($feeType, self::FEE_TYPES, true)) {
            Flash::set('error', 'Invalid fee type.');
            $this->redirect('finance/collections');
            return;
        }
        if (!array_key_exists($method, self::PAYMENT_METHODS)) {
            $method = 'cash';
        }

        // The receipt is rendered live from this row, so updating the row
        // refreshes the receipt automatically. The receipt number stays the same.
        $oldFeeType     = $collection['fee_type'];
        $oldPaymentDate = $collection['payment_date'];

        $this->feeCollection->update($id, [
            'fee_type'       => $feeType,
            'amount_paid'    => $amount,
            'payment_date'   => $paymentDate ?: date('Y-m-d'),
            'payment_method' => $method,
            'notes'          => $notes ?: null,
        ]);

        // Re-sync the Student Fee status for both the previous fee (whose
        // accumulated payments may have changed) and the new one.
        $this->studentFee->syncStatusAfterPayment((int) $collection['student_id'], $oldFeeType, $oldPaymentDate);
        $this->studentFee->syncStatusAfterPayment((int) $collection['student_id'], $feeType, $paymentDate ?: date('Y-m-d'));

        $studentUserId = (int) ((new Student())->firstWhere('id', (int) $collection['student_id'])['user_id'] ?? 0);
        if ($studentUserId > 0) {
            (new Notification())->push(
                $studentUserId,
                'Payment Information Updated',
                'Your payment information has been updated. Your receipt has been updated. Receipt No: ' . $collection['receipt_no'],
                'info',
                'finance/my-receipt/' . (int) $id
            );
        }

        $this->logActivity('update', 'finance', 'Edited payment #' . $id . ' (' . $collection['receipt_no'] . ')');
        Flash::set('success', 'Payment updated and receipt refreshed.');
        $this->redirect('finance/receipt/' . (int) $id);
    }

    /**
     * Void a payment. The payment row is kept for the audit trail but marked
     * voided; the receipt shows VOIDED, the related Student Fee status is
     * re-evaluated without this payment, and the student is notified.
     */
    public function collectionsVoid(int $id): void
    {
        CSRF::check();
        $this->authorize();

        $collection = $this->feeCollection->find($id);
        if (!$collection) {
            Flash::set('error', 'Payment not found.');
            $this->redirect('finance/collections');
            return;
        }
        if (($collection['status'] ?? 'active') === 'voided') {
            Flash::set('info', 'This payment is already voided.');
            $this->redirect('finance/collections');
            return;
        }

        $this->feeCollection->update($id, ['status' => 'voided']);

        // Re-evaluate the related Student Fee without this payment.
        $this->studentFee->syncStatusAfterPayment((int) $collection['student_id'], $collection['fee_type'], $collection['payment_date']);

        $studentUserId = (int) ((new Student())->firstWhere('id', (int) $collection['student_id'])['user_id'] ?? 0);
        if ($studentUserId > 0) {
            (new Notification())->push(
                $studentUserId,
                'Payment Cancelled',
                'Your payment has been cancelled. Receipt status has been updated. Receipt No: ' . $collection['receipt_no'],
                'warning',
                'finance/my-receipt/' . (int) $id
            );
        }

        $this->logActivity('void', 'finance', 'Voided payment #' . $id . ' (' . $collection['receipt_no'] . ')');
        Flash::set('success', 'Payment voided. The receipt is marked voided and the student was notified.');
        $this->redirect('finance/collections');
    }

    /**
     * Number of voided payment records currently stored.
     */
    protected function voidedCount(): int
    {
        return Database::getInstance()->count(
            "SELECT COUNT(*) FROM fee_collections WHERE status = 'voided'"
        );
    }

    /**
     * Remove one voided payment: the fee_collections row and the payment
     * notification(s) sent to the student for its receipt. The receipt is
     * rendered live from the fee_collections row, so deleting the row
     * removes the receipt as well. Student records, user accounts and
     * Student Fee records are never touched, and this never affects the
     * Student Fee cascade delete feature. The caller owns the transaction.
     */
    protected function removeVoidedPayment(array $collection): void
    {
        $db = Database::getInstance();

        $studentUserId = (int) ((new Student())->firstWhere('id', (int) $collection['student_id'])['user_id'] ?? 0);
        if ($studentUserId > 0) {
            $db->delete(
                'notifications',
                'user_id = ? AND link = ?',
                [$studentUserId, 'finance/my-receipt/' . (int) $collection['id']]
            );
        }

        $this->feeCollection->delete((int) $collection['id']);
    }

    /**
     * Delete a single Voided payment record (cleanup). Active, pending and
     * other records are never touched - only the row whose status is voided,
     * together with its receipt and the related payment notification.
     */
    public function collectionsDelete(int $id): void
    {
        CSRF::check();
        $this->authorize();

        $collection = $this->feeCollection->find($id);
        if (!$collection) {
            Flash::set('error', 'Payment not found.');
            $this->redirect('finance/collections');
            return;
        }
        if (($collection['status'] ?? 'active') !== 'voided') {
            Flash::set('error', 'Only voided payments can be deleted.');
            $this->redirect('finance/collections');
            return;
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            $this->removeVoidedPayment($collection);
            $db->commit();

            $this->logActivity('delete', 'finance', 'Deleted voided payment #' . (int) $collection['id'] . ' (' . $collection['receipt_no'] . ')');
            Flash::set('success', 'Voided payment record deleted. The receipt and related notification were removed.');
            $this->redirect('finance/collections');
        } catch (Throwable $e) {
            $db->rollBack();
            Flash::set('error', 'Could not delete the voided payment. Please try again.');
            $this->redirect('finance/collections');
        }
    }

    /**
     * Bulk cleanup: remove every Voided payment record in one go. Same
     * behaviour as deleting a single voided record, applied to all of them.
     */
    public function collectionsDeleteVoided(): void
    {
        CSRF::check();
        $this->authorize();

        $rows = Database::getInstance()->fetchAll(
            "SELECT * FROM fee_collections WHERE status = 'voided' ORDER BY id"
        );
        if (empty($rows)) {
            Flash::set('info', 'There are no voided payment records to remove.');
            $this->redirect('finance/collections');
            return;
        }

        $db = Database::getInstance();
        $db->beginTransaction();
        try {
            foreach ($rows as $row) {
                $this->removeVoidedPayment($row);
            }
            $db->commit();

            $count = count($rows);
            $this->logActivity('delete', 'finance', 'Removed ' . $count . ' voided payment record(s)');
            Flash::set('success', $count . ' voided payment record(s) deleted. Receipts and related notifications were removed.');
            $this->redirect('finance/collections');
        } catch (Throwable $e) {
            $db->rollBack();
            Flash::set('error', 'Could not remove voided payments. Please try again.');
            $this->redirect('finance/collections');
        }
    }

    // ============================================================
    //  3. PAYMENT RECEIPTS
    // ============================================================

    public function receipts(): void
    {
        $this->authorize();

        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $like = '%' . $q . '%';
            $rows = Database::getInstance()->fetchAll(
                'SELECT fc.*, s.first_name, s.last_name, s.admission_no, c.name AS class_name
                 FROM fee_collections fc
                 INNER JOIN students s ON s.id = fc.student_id
                 LEFT JOIN classes c ON c.id = fc.class_id
                 WHERE fc.receipt_no LIKE ? OR s.admission_no LIKE ?
                    OR CONCAT(s.first_name, " ", s.last_name) LIKE ?
                 ORDER BY fc.id DESC LIMIT 100',
                [$like, $like, $like]
            );
        } else {
            $rows = $this->feeCollection->listAll();
        }

        $this->render('finance/receipts', ['q' => $q, 'rows' => $rows]);
    }

    public function receipt(int $id): void
    {
        $this->authorize();

        $receipt = Database::getInstance()->fetch(
            'SELECT fc.*, s.first_name, s.last_name, s.admission_no, c.name AS class_name,
                    u.first_name AS cashier_first, u.last_name AS cashier_last
             FROM fee_collections fc
             INNER JOIN students s ON s.id = fc.student_id
             LEFT JOIN classes c ON c.id = fc.class_id
             LEFT JOIN users u ON u.id = fc.collected_by
             WHERE fc.id = ?',
            [$id]
        );

        if (!$receipt) {
            $this->renderError('Receipt not found.');
            return;
        }

        $this->renderRaw('finance/receipt', ['receipt' => $receipt]);
    }

    // ============================================================
    //  3b. STUDENT PORTAL — MY FEES & RECEIPTS (read-only, own data)
    // ============================================================

    /**
     * Student profile linked to the currently logged-in user, or null.
     * Always derived from the account — never from the request.
     */
    protected function currentStudent(): ?array
    {
        return (new Student())->firstWhere('user_id', Auth::id());
    }

    /**
     * Student portal: list the logged-in student's own fees and payment
     * history. Amounts and balances are read from the existing Finance
     * records (student_fees / fee_collections) — nothing is duplicated.
     */
    public function myFees(): void
    {
        Auth::requireRole(['student']);

        $student = $this->currentStudent();
        if (!$student) {
            Flash::set('warning', 'No student profile is linked to this account.');
            $this->redirect('dashboard/index');
            return;
        }
        $student = (new Student())->findWithDetails((int) $student['id']);

        $db = Database::getInstance();

        $fees = $db->fetchAll(
            'SELECT f.*, c.name AS class_name,
                    COALESCE((
                        SELECT SUM(fc.amount_paid) FROM fee_collections fc
                        WHERE fc.student_id = f.student_id AND fc.fee_type = f.fee_type
                          AND fc.status = "active"
                          AND (f.month IS NULL OR DATE_FORMAT(fc.payment_date, "%Y-%m") = f.month)
                    ), 0) AS amount_paid,
                    (
                        SELECT MAX(fc.payment_date) FROM fee_collections fc
                        WHERE fc.student_id = f.student_id AND fc.fee_type = f.fee_type
                          AND fc.status = "active"
                          AND (f.month IS NULL OR DATE_FORMAT(fc.payment_date, "%Y-%m") = f.month)
                    ) AS payment_date
             FROM student_fees f
             LEFT JOIN classes c ON c.id = f.class_id
             WHERE f.student_id = ?
             ORDER BY f.id DESC',
            [(int) $student['id']]
        );

        $receipts = $db->fetchAll(
            'SELECT fc.*, c.name AS class_name,
                    u.first_name AS cashier_first, u.last_name AS cashier_last
             FROM fee_collections fc
             LEFT JOIN classes c ON c.id = fc.class_id
             LEFT JOIN users u ON u.id = fc.collected_by
             WHERE fc.student_id = ?
             ORDER BY fc.payment_date DESC, fc.id DESC',
            [(int) $student['id']]
        );

        $this->render('finance/my_fees', [
            'student'  => $student,
            'fees'     => $fees,
            'receipts' => $receipts,
        ]);
    }

    /**
     * Student portal: printable receipt for one of the logged-in
     * student's own payments. Returns 404 unless the receipt belongs to
     * the student linked to the current account.
     */
    public function myReceipt(int $id): void
    {
        Auth::requireRole(['student']);

        $student = $this->currentStudent();
        if (!$student) {
            Flash::set('warning', 'No student profile is linked to this account.');
            $this->redirect('dashboard/index');
            return;
        }

        $receipt = Database::getInstance()->fetch(
            'SELECT fc.*, s.first_name, s.middle_name, s.last_name, s.admission_no, c.name AS class_name,
                    u.first_name AS cashier_first, u.last_name AS cashier_last
             FROM fee_collections fc
             INNER JOIN students s ON s.id = fc.student_id
             LEFT JOIN classes c ON c.id = fc.class_id
             LEFT JOIN users u ON u.id = fc.collected_by
             WHERE fc.id = ? AND fc.student_id = ?',
            [$id, (int) $student['id']]
        );

        if (!$receipt) {
            $this->renderError('Receipt not found.');
            return;
        }

        $this->renderRaw('finance/receipt', ['receipt' => $receipt]);
    }

    // ============================================================
    //  4. STAFF PAYROLL
    // ============================================================

    public function payroll(): void
    {
        $this->authorize();

        $rows  = $this->payroll->listAll();
        $total = count($rows);
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $pagination = new Pagination($total, $page, PAGINATION_LIMIT);
        $rows = array_slice($rows, $pagination->offset(), PAGINATION_LIMIT);

        $this->render('finance/payroll', [
            'rows'        => $rows,
            'pagination'  => $pagination,
            'teachers'    => (new Teacher())->listAll(),
            'staff'       => (new Staff())->all('first_name'),
            'months'      => $this->monthsList(),
            'totalPayroll' => $this->payroll->totalAmount(),
        ]);
    }

    public function payrollStore(): void
    {
        CSRF::check();
        $this->authorize();

        $employeeType = trim($_POST['employee_type'] ?? '');
        $employeeId   = (int) ($_POST['employee_id'] ?? 0);
        $salaryAmount = (float) ($_POST['salary_amount'] ?? 0);
        $month        = trim($_POST['month'] ?? date('Y-m'));
        $paymentDate  = trim($_POST['payment_date'] ?? date('Y-m-d'));
        $status       = in_array(trim($_POST['status'] ?? ''), ['paid', 'pending'], true)
                        ? $_POST['status'] : 'paid';
        $notes        = trim($_POST['notes'] ?? '');

        $position = '';
        $employee = null;
        if ($employeeType === 'teacher') {
            $employee = (new Teacher())->find($employeeId);
            $position = $employee['department'] ?? '';
        } elseif ($employeeType === 'staff') {
            $employee = (new Staff())->find($employeeId);
            $position = ($employee['designation'] ?? '') ?: ($employee['department'] ?? '');
        }

        if (!$employee || $salaryAmount <= 0) {
            Flash::set('error', 'Select an employee and enter a valid salary amount.');
            $this->redirect('finance/payroll');
        }

        $this->payroll->create([
            'employee_type'  => $employeeType,
            'employee_id'    => $employeeId,
            'position'       => $position ?: null,
            'salary_amount'  => $salaryAmount,
            'month'          => $month ?: date('Y-m'),
            'payment_date'   => $paymentDate ?: date('Y-m-d'),
            'status'         => $status,
            'notes'          => $notes ?: null,
            'created_by'     => $this->userId(),
        ]);

        $this->logActivity('create', 'finance', 'Payroll record for ' . $employeeType . ' #' . $employeeId);
        Flash::set('success', 'Payroll record saved.');
        $this->redirect('finance/payroll');
    }

    public function payrollDelete(int $id): void
    {
        CSRF::check();
        $this->authorize();

        $this->payroll->delete($id);
        $this->logActivity('delete', 'finance', 'Deleted payroll record #' . $id);
        Flash::set('success', 'Payroll record deleted.');
        $this->redirect('finance/payroll');
    }

    // ============================================================
    //  5. SCHOOL EXPENSES
    // ============================================================

    public function expenses(): void
    {
        $this->authorize();

        $categoryId = (int) ($_GET['category_id'] ?? 0);

        $rows  = $this->expense->listAll($categoryId ?: null);
        $total = count($rows);
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $pagination = new Pagination($total, $page, PAGINATION_LIMIT);
        $rows = array_slice($rows, $pagination->offset(), PAGINATION_LIMIT);

        $this->render('finance/expenses', [
            'rows'          => $rows,
            'pagination'    => $pagination,
            'categoryId'    => $categoryId,
            'categories'    => $this->expenseCategory->all('name'),
            'totalExpenses' => $this->expense->totalAmount(),
        ]);
    }

    public function expensesStore(): void
    {
        CSRF::check();
        $this->authorize();

        $categoryId  = (int) ($_POST['category_id'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $amount      = (float) ($_POST['amount'] ?? 0);
        $expenseDate = trim($_POST['expense_date'] ?? date('Y-m-d'));
        $notes       = trim($_POST['notes'] ?? '');

        if (!$categoryId || $description === '' || $amount <= 0) {
            Flash::set('error', 'Category, description and a valid amount are required.');
            $this->redirect('finance/expenses');
        }

        $this->expense->create([
            'category_id'  => $categoryId,
            'description'  => $description,
            'amount'       => $amount,
            'expense_date' => $expenseDate ?: date('Y-m-d'),
            'paid_by'      => $this->userId(),
            'notes'        => $notes ?: null,
        ]);

        $this->logActivity('create', 'finance', 'Recorded expense: ' . $description);
        Flash::set('success', 'Expense recorded.');
        $this->redirect('finance/expenses');
    }

    public function expensesDelete(int $id): void
    {
        CSRF::check();
        $this->authorize();

        $this->expense->delete($id);
        $this->logActivity('delete', 'finance', 'Deleted expense #' . $id);
        Flash::set('success', 'Expense deleted.');
        $this->redirect('finance/expenses');
    }

    // ============================================================
    //  6. EXPENSE CATEGORIES
    // ============================================================

    public function categories(): void
    {
        $this->authorize();

        $this->render('finance/categories', [
            'rows' => $this->expenseCategory->all('name'),
        ]);
    }

    public function categoriesStore(): void
    {
        CSRF::check();
        $this->authorize();

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            Flash::set('error', 'Category name is required.');
            $this->redirect('finance/categories');
        }
        if ($this->expenseCategory->firstWhere('name', $name)) {
            Flash::set('error', 'A category with this name already exists.');
            $this->redirect('finance/categories');
        }

        $this->expenseCategory->create([
            'name'        => $name,
            'description' => trim($_POST['description'] ?? ''),
        ]);

        $this->logActivity('create', 'finance', 'Added expense category: ' . $name);
        Flash::set('success', 'Expense category added.');
        $this->redirect('finance/categories');
    }

    public function categoriesDelete(int $id): void
    {
        CSRF::check();
        $this->authorize();

        $inUse = Database::getInstance()->count(
            'SELECT COUNT(*) FROM expenses WHERE category_id = ?',
            [$id]
        );
        if ($inUse > 0) {
            Flash::set('error', 'This category is used by expenses and cannot be deleted.');
            $this->redirect('finance/categories');
        }

        $this->expenseCategory->delete($id);
        $this->logActivity('delete', 'finance', 'Deleted expense category #' . $id);
        Flash::set('success', 'Expense category deleted.');
        $this->redirect('finance/categories');
    }

    // ============================================================
    //  7. FINANCE REPORTS
    // ============================================================

    public function reports(): void
    {
        $this->authorize();

        $type = $this->reportType();
        $from = trim($_GET['from'] ?? '');
        $to   = trim($_GET['to'] ?? '');

        $this->render('finance/reports', [
            'type' => $type,
            'from' => $from,
            'to'   => $to,
            'report' => $this->reportData($type, $from, $to),
        ]);
    }

    public function reportPrint(): void
    {
        $this->authorize();

        $type = $this->reportType();
        $from = trim($_GET['from'] ?? '');
        $to   = trim($_GET['to'] ?? '');

        $this->renderRaw('finance/report_print', [
            'type' => $type,
            'from' => $from,
            'to'   => $to,
            'report' => $this->reportData($type, $from, $to),
        ]);
    }

    protected function reportType(): string
    {
        $type = trim($_GET['type'] ?? 'student-fees');
        $allowed = [
            'student-fees', 'pending-fees', 'collections',
            'payroll', 'expenses', 'monthly-summary',
        ];
        return in_array($type, $allowed, true) ? $type : 'student-fees';
    }

    /**
     * Build display-ready rows (pre-formatted strings) for a report.
     */
    protected function reportData(string $type, string $from = '', string $to = ''): array
    {
        $db = Database::getInstance();

        switch ($type) {
            case 'pending-fees':
                $rows = $db->fetchAll(
                    'SELECT f.*, s.first_name, s.last_name, s.admission_no, c.name AS class_name
                     FROM student_fees f
                     INNER JOIN students s ON s.id = f.student_id
                     LEFT JOIN classes c ON c.id = f.class_id
                     WHERE f.status IN ("pending", "partial")
                     ORDER BY f.due_date ASC, f.id ASC'
                );
                return [
                    'title'   => 'Pending Fees Report',
                    'columns' => [
                        ['key' => 'student',    'label' => 'Student'],
                        ['key' => 'admission',  'label' => 'Admission No'],
                        ['key' => 'class',      'label' => 'Class'],
                        ['key' => 'fee_type',   'label' => 'Fee Type'],
                        ['key' => 'session',    'label' => 'Session'],
                        ['key' => 'amount',     'label' => 'Amount'],
                        ['key' => 'due_date',   'label' => 'Due Date'],
                        ['key' => 'status',     'label' => 'Status'],
                    ],
                    'rows' => array_map(fn(array $f) => [
                        'student'   => studentName($f, $f['admission_no']),
                        'admission' => $f['admission_no'],
                        'class'     => $f['class_name'] ?: '—',
                        'fee_type'  => $f['fee_type'],
                        'session'   => $f['academic_session'],
                        'amount'    => currency((float) $f['amount']),
                        'due_date'  => formatDate($f['due_date']),
                        'status'    => ucfirst($f['status']),
                    ], $rows),
                    'totals' => [
                        'Pending Fees' => currency((float) $db->fetchColumn(
                            "SELECT COALESCE(SUM(amount),0) FROM student_fees WHERE status = 'pending'"
                        )),
                        'Partially Paid' => currency((float) $db->fetchColumn(
                            "SELECT COALESCE(SUM(amount),0) FROM student_fees WHERE status = 'partial'"
                        )),
                        'Total Outstanding' => currency((float) $db->fetchColumn(
                            "SELECT COALESCE(SUM(amount),0) FROM student_fees WHERE status IN ('pending','partial')"
                        )),
                    ],
                ];

            case 'collections':
                $where = '1 = 1';
                $params = [];
                if ($from !== '') {
                    $where .= ' AND fc.payment_date >= ?';
                    $params[] = $from;
                }
                if ($to !== '') {
                    $where .= ' AND fc.payment_date <= ?';
                    $params[] = $to;
                }
                $rows = $db->fetchAll(
                    'SELECT fc.*, s.first_name, s.last_name, s.admission_no, c.name AS class_name,
                            u.first_name AS cashier_first, u.last_name AS cashier_last
                     FROM fee_collections fc
                     INNER JOIN students s ON s.id = fc.student_id
                     LEFT JOIN classes c ON c.id = fc.class_id
                     LEFT JOIN users u ON u.id = fc.collected_by
                     WHERE ' . $where . '
                     ORDER BY fc.payment_date DESC, fc.id DESC',
                    $params
                );
                return [
                    'title'   => 'Fee Collection Report',
                    'columns' => [
                        ['key' => 'receipt',   'label' => 'Receipt No'],
                        ['key' => 'student',    'label' => 'Student'],
                        ['key' => 'class',      'label' => 'Class'],
                        ['key' => 'fee_type',   'label' => 'Fee Type'],
                        ['key' => 'amount',     'label' => 'Amount Paid'],
                        ['key' => 'date',       'label' => 'Payment Date'],
                        ['key' => 'method',     'label' => 'Method'],
                        ['key' => 'received',   'label' => 'Received By'],
                        ['key' => 'status',     'label' => 'Status'],
                    ],
                    'rows' => array_map(fn(array $r) => [
                        'receipt'  => $r['receipt_no'],
                        'student'  => studentName($r, $r['admission_no']),
                        'class'    => $r['class_name'] ?: '—',
                        'fee_type' => $r['fee_type'],
                        'amount'   => currency((float) $r['amount_paid']),
                        'date'     => formatDate($r['payment_date']),
                        'method'   => ucwords(str_replace('_', ' ', $r['payment_method'])),
                        'received' => trim(($r['cashier_first'] ?? '') . ' ' . ($r['cashier_last'] ?? '')) ?: '—',
                        'status'   => ($r['status'] ?? 'active') === 'voided' ? 'Voided' : 'Active',
                    ], $rows),
                    'totals' => [
                        'Total Collected' => currency((float) $db->fetchColumn(
                            'SELECT COALESCE(SUM(amount_paid),0) FROM fee_collections fc WHERE ' . $where . ' AND fc.status = ?',
                            array_merge($params, ['active'])
                        )),
                    ],
                ];

            case 'payroll':
                $rows = $db->fetchAll(
                    'SELECT p.*,
                            COALESCE(t.first_name, st.first_name) AS first_name,
                            COALESCE(t.last_name, st.last_name) AS last_name,
                            COALESCE(t.employee_id, st.employee_id) AS employee_id
                     FROM payrolls p
                     LEFT JOIN teachers t ON t.id = p.employee_id AND p.employee_type = "teacher"
                     LEFT JOIN staff st ON st.id = p.employee_id AND p.employee_type = "staff"
                     ORDER BY p.month DESC, p.id DESC'
                );
                return [
                    'title'   => 'Payroll Report',
                    'columns' => [
                        ['key' => 'employee', 'label' => 'Employee'],
                        ['key' => 'emp_id',   'label' => 'Employee ID'],
                        ['key' => 'position', 'label' => 'Position'],
                        ['key' => 'amount',   'label' => 'Salary Amount'],
                        ['key' => 'month',    'label' => 'Month'],
                        ['key' => 'date',     'label' => 'Payment Date'],
                        ['key' => 'status',   'label' => 'Status'],
                    ],
                    'rows' => array_map(fn(array $r) => [
                        'employee' => trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? '')) ?: '—',
                        'emp_id'   => $r['employee_id'] ?? '—',
                        'position' => $r['position'] ?: '—',
                        'amount'   => currency((float) $r['salary_amount']),
                        'month'    => ldate('F Y', strtotime(($r['month'] ?? '') . '-01')),
                        'date'     => formatDate($r['payment_date']),
                        'status'   => ucfirst($r['status']),
                    ], $rows),
                    'totals' => [
                        'Total Payroll' => currency($this->payroll->totalAmount()),
                    ],
                ];

            case 'expenses':
                $where = '1 = 1';
                $params = [];
                if ($from !== '') {
                    $where .= ' AND e.expense_date >= ?';
                    $params[] = $from;
                }
                if ($to !== '') {
                    $where .= ' AND e.expense_date <= ?';
                    $params[] = $to;
                }
                $rows = $db->fetchAll(
                    'SELECT e.*, ec.name AS category_name,
                            u.first_name AS payer_first, u.last_name AS payer_last
                     FROM expenses e
                     INNER JOIN expense_categories ec ON ec.id = e.category_id
                     LEFT JOIN users u ON u.id = e.paid_by
                     WHERE ' . $where . '
                     ORDER BY e.expense_date DESC, e.id DESC',
                    $params
                );
                return [
                    'title'   => 'Expense Report',
                    'columns' => [
                        ['key' => 'date',       'label' => 'Date'],
                        ['key' => 'category',   'label' => 'Category'],
                        ['key' => 'description','label' => 'Description'],
                        ['key' => 'amount',     'label' => 'Amount'],
                        ['key' => 'paid_by',    'label' => 'Paid By'],
                    ],
                    'rows' => array_map(fn(array $r) => [
                        'date'        => formatDate($r['expense_date']),
                        'category'    => $r['category_name'],
                        'description' => $r['description'],
                        'amount'      => currency((float) $r['amount']),
                        'paid_by'     => trim(($r['payer_first'] ?? '') . ' ' . ($r['payer_last'] ?? '')) ?: '—',
                    ], $rows),
                    'totals' => [
                        'Total Expenses' => currency((float) $db->fetchColumn(
                            'SELECT COALESCE(SUM(amount),0) FROM expenses e WHERE ' . $where,
                            $params
                        )),
                    ],
                ];

            case 'monthly-summary':
                $income = $db->fetchAll(
                    "SELECT DATE_FORMAT(payment_date, '%Y-%m') AS ym, SUM(amount_paid) AS total
                     FROM fee_collections WHERE status = 'active' GROUP BY ym ORDER BY ym DESC"
                );
                $spent = $db->fetchAll(
                    "SELECT DATE_FORMAT(expense_date, '%Y-%m') AS ym, SUM(amount) AS total
                     FROM expenses GROUP BY ym ORDER BY ym DESC"
                );
                $incomeMap = [];
                $spentMap = [];
                foreach ($income as $row) {
                    $incomeMap[$row['ym']] = (float) $row['total'];
                }
                foreach ($spent as $row) {
                    $spentMap[$row['ym']] = (float) $row['total'];
                }
                $months = array_unique(array_merge(array_keys($incomeMap), array_keys($spentMap)));
                rsort($months);

                $rows = [];
                foreach ($months as $ym) {
                    $inc = $incomeMap[$ym] ?? 0.0;
                    $exp = $spentMap[$ym] ?? 0.0;
                    $rows[] = [
                        'month'    => ldate('F Y', strtotime($ym . '-01')),
                        'income'   => currency($inc),
                        'expenses' => currency($exp),
                        'balance'  => currency($inc - $exp),
                    ];
                }
                $totalIncome = (float) $db->fetchColumn("SELECT COALESCE(SUM(amount_paid),0) FROM fee_collections WHERE status = 'active'");
                $totalSpent  = (float) $db->fetchColumn('SELECT COALESCE(SUM(amount),0) FROM expenses');
                return [
                    'title'   => 'Monthly Finance Summary',
                    'columns' => [
                        ['key' => 'month',    'label' => 'Month'],
                        ['key' => 'income',   'label' => 'Income'],
                        ['key' => 'expenses', 'label' => 'Expenses'],
                        ['key' => 'balance',  'label' => 'Balance'],
                    ],
                    'rows' => $rows,
                    'totals' => [
                        'Total Income'   => currency($totalIncome),
                        'Total Expenses' => currency($totalSpent),
                        'Net Balance'    => currency($totalIncome - $totalSpent),
                    ],
                ];

            case 'student-fees':
            default:
                $rows = $db->fetchAll(
                    'SELECT f.*, s.first_name, s.last_name, s.admission_no, c.name AS class_name
                     FROM student_fees f
                     INNER JOIN students s ON s.id = f.student_id
                     LEFT JOIN classes c ON c.id = f.class_id
                     ORDER BY f.id DESC'
                );
                return [
                    'title'   => 'Student Fee Report',
                    'columns' => [
                        ['key' => 'student',   'label' => 'Student'],
                        ['key' => 'admission', 'label' => 'Admission No'],
                        ['key' => 'class',     'label' => 'Class'],
                        ['key' => 'fee_type',  'label' => 'Fee Type'],
                        ['key' => 'session',   'label' => 'Session'],
                        ['key' => 'month',     'label' => 'Month'],
                        ['key' => 'amount',    'label' => 'Amount'],
                        ['key' => 'due_date',  'label' => 'Due Date'],
                        ['key' => 'status',    'label' => 'Status'],
                    ],
                    'rows' => array_map(fn(array $f) => [
                        'student'   => studentName($f, $f['admission_no']),
                        'admission' => $f['admission_no'],
                        'class'     => $f['class_name'] ?: '—',
                        'fee_type'  => $f['fee_type'],
                        'session'   => $f['academic_session'],
                        'month'     => $f['month'] ?: '—',
                        'amount'    => currency((float) $f['amount']),
                        'due_date'  => formatDate($f['due_date']),
                        'status'    => ucfirst($f['status']),
                    ], $rows),
                    'totals' => [
                        'Total Fees Recorded' => currency($this->studentFee->grandTotal()),
                    ],
                ];
        }
    }
}
