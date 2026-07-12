<?php
/** AstraCampus - Payment Controller */

class PaymentController
{
    public static function index(): void
    {
        require_role(['developer', 'admin', 'accountant', 'teacher']);
        $classes = ClassModel::all(true);
        $filter = $_GET['class_filter'] ?? 'all_active';
        $classId = null;

        if ($filter !== 'all_active' && $filter !== 'graduates' && is_numeric($filter)) {
            $classId = (int) $filter;
        }

        // Teachers restricted to their class
        if (is_teacher() && !is_admin()) {
            $classId = current_user()['class_assigned'];
        }

        $students = Student::allActiveAndGraduatesForPayments($classId);
        if ($filter === 'graduates' && !is_teacher()) {
            $students = array_values(array_filter($students, fn($s) => $s['status'] === 'Graduated'));
        } elseif ($filter === 'all_active' || $classId) {
            $students = array_values(array_filter($students, fn($s) => $s['status'] === 'Active'));
        }

        $recentPayments = Payment::recent(50);

        require __DIR__ . '/../views/payments/index.php';
    }

    public static function payForm(array $params): void
    {
        require_role(['developer', 'admin', 'accountant', 'teacher']);
        $id = (int) $params['id'];
        $student = Student::find($id);
        if (!$student) { flash('error', 'Student not found.'); redirect('/payments'); }

        $unpaidBills = Bill::unpaidForStudent($id);
        $balance = Student::balanceFor($id);
        $mpesaConfigured = mpesa_is_configured();

        require __DIR__ . '/../views/payments/pay_modal_page.php';
    }

    public static function store(): void
    {
        require_role(['developer', 'admin', 'accountant', 'teacher']);
        verify_csrf();

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $billId = (int) ($_POST['bill_id'] ?? 0);
        $amount = (float) ($_POST['amount'] ?? 0);
        $method = clean_input($_POST['payment_method'] ?? 'Cash');
        $payerName = clean_input($_POST['payer_name'] ?? '');

        $student = Student::find($studentId);
        $bill = Bill::find($billId);

        if (!$student || !$bill) {
            flash('error', 'Invalid student or bill selected.');
            redirect('/payments');
        }

        if ($amount <= 0) {
            flash('error', 'Payment amount must be greater than zero.');
            redirect('/payments/' . $studentId . '/pay');
        }

        if ($amount > (float) $bill['remaining'] + 0.009) {
            flash('error', 'Payment amount exceeds the remaining balance for this bill.');
            redirect('/payments/' . $studentId . '/pay');
        }

        $details = null;
        if ($method === 'M-Pesa') {
            $txnCode = clean_input($_POST['mpesa_code'] ?? '');
            $phone = clean_input($_POST['mpesa_phone'] ?? '');
            if (empty($txnCode) || empty($phone)) {
                flash('error', 'M-Pesa transaction code and phone number are required.');
                redirect('/payments/' . $studentId . '/pay');
            }
            $details = ['transaction_code' => $txnCode, 'phone' => $phone];
        }

        $paymentId = Payment::create([
            'student_id'       => $studentId,
            'bill_id'          => $billId,
            'amount_paid'      => $amount,
            'payment_method'   => $method,
            'payment_details'  => $details,
            'payer_name'       => $payerName,
            'collected_by'     => current_user()['id'],
        ]);

        audit_log('payment', 'payments', $paymentId, null,
            ['student_id' => $studentId, 'amount' => $amount, 'method' => $method],
            'Payment processed');

        flash('success', 'Payment processed successfully.');
        redirect('/receipts/' . $paymentId);
    }

    // ---------------- M-Pesa STK Push ----------------

    /** AJAX: initiate an STK push prompt to the customer's phone. Responds with JSON. */
    public static function mpesaStkPush(): void
    {
        header('Content-Type: application/json');
        require_role(['developer', 'admin', 'accountant', 'teacher']);
        verify_csrf();

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $billId = (int) ($_POST['bill_id'] ?? 0);
        $amount = (float) ($_POST['amount'] ?? 0);
        $phone = clean_input($_POST['phone'] ?? '');

        $student = Student::find($studentId);
        $bill = Bill::find($billId);

        if (!$student || !$bill) {
            echo json_encode(['success' => false, 'message' => 'Invalid student or bill selected.']);
            exit;
        }
        if ($amount <= 0 || $amount > (float) $bill['remaining'] + 0.009) {
            echo json_encode(['success' => false, 'message' => 'Enter a valid amount that does not exceed the remaining balance.']);
            exit;
        }
        if (empty($phone)) {
            echo json_encode(['success' => false, 'message' => 'Phone number is required.']);
            exit;
        }

        $result = mpesa_stk_push($phone, $amount, $student['admission_no'], 'School Fees');

        if (!empty($result['success'])) {
            MpesaTransaction::create([
                'student_id'          => $studentId,
                'bill_id'             => $billId,
                'phone'               => mpesa_normalize_phone($phone),
                'amount'              => $amount,
                'merchant_request_id' => $result['merchant_request_id'],
                'checkout_request_id' => $result['checkout_request_id'],
                'initiated_by'        => current_user()['id'],
            ]);
            audit_log('mpesa_stk_push', 'mpesa_transactions', null, null,
                ['student_id' => $studentId, 'amount' => $amount, 'phone' => $phone],
                'STK push sent to customer');

            echo json_encode(['success' => true, 'message' => $result['message'], 'checkout_request_id' => $result['checkout_request_id']]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => $result['message']]);
        exit;
    }

    /** AJAX: frontend polls this while waiting for the customer to complete the prompt. */
    public static function mpesaStatus(array $params): void
    {
        header('Content-Type: application/json');
        require_role(['developer', 'admin', 'accountant', 'teacher']);

        $checkoutId = $params['checkout_id'] ?? '';
        $txn = MpesaTransaction::findByCheckoutId($checkoutId);

        if (!$txn) {
            echo json_encode(['status' => 'unknown']);
            exit;
        }

        echo json_encode([
            'status'     => $txn['status'],
            'payment_id' => $txn['payment_id'],
            'message'    => $txn['result_desc'],
        ]);
        exit;
    }

    /**
     * Public endpoint — Safaricom POSTs the STK push result here. No login/CSRF
     * (Safaricom cannot present either), so this must stay narrowly scoped to
     * only acting on transactions that we ourselves initiated and recorded.
     */
    public static function mpesaCallback(): void
    {
        header('Content-Type: application/json');

        $raw = file_get_contents('php://input');
        $input = json_decode($raw, true);
        $stkCallback = $input['Body']['stkCallback'] ?? null;

        if (!$stkCallback || empty($stkCallback['CheckoutRequestID'])) {
            echo json_encode(['ResultCode' => 1, 'ResultDesc' => 'Invalid callback payload']);
            exit;
        }

        $checkoutId = $stkCallback['CheckoutRequestID'];
        $resultCode = (string) ($stkCallback['ResultCode'] ?? '1');
        $resultDesc = $stkCallback['ResultDesc'] ?? '';

        $txn = MpesaTransaction::findByCheckoutId($checkoutId);
        if (!$txn) {
            // Unknown transaction — acknowledge so Safaricom stops retrying, but do nothing.
            echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            exit;
        }

        if ($resultCode !== '0') {
            $status = $resultCode === '1032' ? 'cancelled' : 'failed';
            MpesaTransaction::markResult($checkoutId, $status, $resultCode, $resultDesc, null, $stkCallback);
            echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
            exit;
        }

        // Success — extract the metadata Safaricom sends back.
        $metadata = [];
        foreach ($stkCallback['CallbackMetadata']['Item'] ?? [] as $item) {
            if (isset($item['Name'])) {
                $metadata[$item['Name']] = $item['Value'] ?? null;
            }
        }
        $mpesaReceipt = $metadata['MpesaReceiptNumber'] ?? null;
        $amountPaid = (float) ($metadata['Amount'] ?? $txn['amount']);
        $payerPhone = (string) ($metadata['PhoneNumber'] ?? $txn['phone']);

        $bill = Bill::find((int) $txn['bill_id']);
        if ($bill && $amountPaid > 0 && $amountPaid <= (float) $bill['remaining'] + 0.009) {
            $paymentId = Payment::create([
                'student_id'      => $txn['student_id'],
                'bill_id'         => $txn['bill_id'],
                'amount_paid'     => $amountPaid,
                'payment_method'  => 'M-Pesa',
                'payment_details' => ['transaction_code' => $mpesaReceipt, 'phone' => $payerPhone, 'via' => 'STK Push'],
                'payer_name'      => null,
                'collected_by'    => $txn['initiated_by'],
            ]);

            MpesaTransaction::markResult($checkoutId, 'success', $resultCode, $resultDesc, $mpesaReceipt, $stkCallback, $paymentId);
            audit_log('payment', 'payments', $paymentId, null,
                ['student_id' => $txn['student_id'], 'amount' => $amountPaid, 'method' => 'M-Pesa (STK Push)'],
                'Payment received via M-Pesa STK push');
        } else {
            // Amount no longer valid against the bill (e.g. already paid elsewhere in the meantime).
            MpesaTransaction::markResult($checkoutId, 'failed', $resultCode, 'Amount could not be applied to the bill (already settled or exceeds balance).', $mpesaReceipt, $stkCallback);
        }

        echo json_encode(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
        exit;
    }
}
