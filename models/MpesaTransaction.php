<?php
/** AstraCampus - M-Pesa Transaction Model */

class MpesaTransaction
{
    public static function create(array $d): int
    {
        $stmt = db()->prepare(
            'INSERT INTO mpesa_transactions
                (student_id, bill_id, phone, amount, merchant_request_id, checkout_request_id, initiated_by)
             VALUES (:student_id, :bill_id, :phone, :amount, :merchant_request_id, :checkout_request_id, :initiated_by)'
        );
        $stmt->execute([
            'student_id'           => $d['student_id'],
            'bill_id'              => $d['bill_id'],
            'phone'                => $d['phone'],
            'amount'               => $d['amount'],
            'merchant_request_id'  => $d['merchant_request_id'],
            'checkout_request_id'  => $d['checkout_request_id'],
            'initiated_by'         => $d['initiated_by'],
        ]);
        return (int) db()->lastInsertId();
    }

    public static function findByCheckoutId(string $checkoutRequestId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM mpesa_transactions WHERE checkout_request_id = :id');
        $stmt->execute(['id' => $checkoutRequestId]);
        return $stmt->fetch() ?: null;
    }

    public static function markResult(string $checkoutRequestId, string $status, ?string $resultCode, ?string $resultDesc, ?string $mpesaReceipt, ?array $rawCallback, ?int $paymentId = null): void
    {
        $stmt = db()->prepare(
            'UPDATE mpesa_transactions SET
                status = :status, result_code = :result_code, result_desc = :result_desc,
                mpesa_receipt_number = :receipt, raw_callback = :raw, payment_id = :payment_id
             WHERE checkout_request_id = :id'
        );
        $stmt->execute([
            'status'      => $status,
            'result_code' => $resultCode,
            'result_desc' => $resultDesc,
            'receipt'     => $mpesaReceipt,
            'raw'         => $rawCallback !== null ? json_encode($rawCallback) : null,
            'payment_id'  => $paymentId,
            'id'          => $checkoutRequestId,
        ]);
    }
}
