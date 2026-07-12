<?php
/** AstraCampus - Receipt Controller */

class ReceiptController
{
    public static function show(array $params): void
    {
        require_role(['developer', 'admin', 'accountant', 'teacher']);
        $id = (int) $params['id'];
        $payment = Payment::find($id);
        if (!$payment) { flash('error', 'Receipt not found.'); redirect('/payments'); }

        $bills = Bill::forStudent((int) $payment['student_id']);
        $termsGrouped = [];
        foreach ($bills as $b) {
            $termsGrouped[$b['term']][] = $b;
        }

        $totalBilled = array_sum(array_column($bills, 'final_amount'));
        $totalPaid = array_sum(array_column($bills, 'paid_amount'));
        $outstanding = $totalBilled - $totalPaid;

        require __DIR__ . '/../views/payments/receipt.php';
    }

    public static function reprint(array $params): void
    {
        require_role(['developer', 'admin', 'accountant', 'teacher']);
        verify_csrf();
        $id = (int) $params['id'];
        $payment = Payment::find($id);
        if (!$payment) { flash('error', 'Receipt not found.'); redirect('/payments'); }

        $reason = clean_input($_POST['reason'] ?? 'Reprint requested');
        Payment::addReprint($payment['receipt_no'], current_user()['id'], $payment['payment_date'], $reason);
        audit_log('reprint_receipt', 'payments', $id, null, ['reason' => $reason], 'Receipt reprinted');
        flash('success', 'Reprint logged.');
        redirect('/receipts/' . $id);
    }

    public static function sendSms(array $params): void
    {
        require_role(['developer', 'admin', 'accountant', 'teacher']);
        verify_csrf();
        $id = (int) $params['id'];
        $payment = Payment::find($id);
        if (!$payment) { flash('error', 'Receipt not found.'); redirect('/payments'); }

        $phone = clean_input($_POST['phone'] ?? '');
        $message = clean_input($_POST['message'] ?? '');

        if (empty($phone) || empty($message)) {
            flash('error', 'Phone number and message are required.');
            redirect('/receipts/' . $id);
        }
        if (strlen($message) > 160) {
            $message = substr($message, 0, 160);
        }

        send_sms($phone, $message, $payment['guardian1_name'] ?? null, 'receipt');
        audit_log('send_sms', 'messages', null, null, ['phone' => $phone], 'SMS receipt sent');
        flash('success', 'SMS sent successfully.');
        redirect('/receipts/' . $id);
    }
}
