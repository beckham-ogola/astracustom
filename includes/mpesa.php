<?php
/**
 * AstraCampus - M-Pesa STK Push Integration
 *
 * Implements the Safaricom Daraja "Lipa na M-Pesa Online" (STK Push) flow:
 * we ask Safaricom to prompt the payer's phone for their M-Pesa PIN, then
 * Safaricom calls our callback URL with the result. See config/mpesa.php
 * for how to configure your Till Number and API credentials.
 *
 * Requires the PHP curl extension.
 */

require_once __DIR__ . '/../config/mpesa.php';

function mpesa_get_access_token(): ?string
{
    if (!mpesa_is_configured()) {
        return null;
    }

    // Cache the token for its lifetime (tokens are valid ~1 hour) to avoid
    // re-authenticating on every request.
    if (!empty($_SESSION['mpesa_token']) && !empty($_SESSION['mpesa_token_expires']) && time() < $_SESSION['mpesa_token_expires']) {
        return $_SESSION['mpesa_token'];
    }

    $c = mpesa_config();

    if (!function_exists('curl_init')) {
        error_log('M-Pesa error: the PHP curl extension is not enabled.');
        return null;
    }

    $ch = curl_init(mpesa_base_url() . '/oauth/v1/generate?grant_type=client_credentials');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, $c['consumer_key'] . ':' . $c['consumer_secret']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || !$response) {
        error_log('M-Pesa token request failed: ' . $error);
        return null;
    }

    $data = json_decode($response, true);
    if (empty($data['access_token'])) {
        error_log('M-Pesa token response invalid: ' . $response);
        return null;
    }

    $_SESSION['mpesa_token'] = $data['access_token'];
    $_SESSION['mpesa_token_expires'] = time() + (int) ($data['expires_in'] ?? 3599) - 60;

    return $data['access_token'];
}

/** Normalizes a Kenyan phone number to the 2547XXXXXXXX / 2541XXXXXXXX format Safaricom expects. */
function mpesa_normalize_phone(string $phone): string
{
    $phone = preg_replace('/\D+/', '', $phone);
    if (strpos($phone, '0') === 0) {
        $phone = '254' . substr($phone, 1);
    } elseif (strpos($phone, '254') !== 0 && (strpos($phone, '7') === 0 || strpos($phone, '1') === 0)) {
        $phone = '254' . $phone;
    }
    return $phone;
}

/**
 * Sends an STK push prompt to the payer's phone for the given amount.
 * Returns ['success' => bool, 'message' => string, 'checkout_request_id' => ?string, 'merchant_request_id' => ?string]
 */
function mpesa_stk_push(string $phone, float $amount, string $accountReference, string $description): array
{
    if (!mpesa_is_configured()) {
        return ['success' => false, 'message' => 'M-Pesa is not configured yet. Add your Till Number, Passkey, and API credentials in Settings → M-Pesa Integration.'];
    }

    if (!function_exists('curl_init')) {
        return ['success' => false, 'message' => 'The PHP curl extension is required for M-Pesa but is not enabled on this server.'];
    }

    $token = mpesa_get_access_token();
    if (!$token) {
        return ['success' => false, 'message' => 'Could not authenticate with M-Pesa. Check your Consumer Key and Consumer Secret.'];
    }

    $c = mpesa_config();
    $timestamp = date('YmdHis');
    $password = base64_encode($c['shortcode'] . $c['passkey'] . $timestamp);
    $normalizedPhone = mpesa_normalize_phone($phone);

    $payload = [
        'BusinessShortCode' => $c['shortcode'],
        'Password'          => $password,
        'Timestamp'         => $timestamp,
        'TransactionType'   => $c['transaction_type'],
        'Amount'            => (int) round($amount),
        'PartyA'            => $normalizedPhone,
        'PartyB'            => $c['shortcode'],
        'PhoneNumber'       => $normalizedPhone,
        'CallBackURL'       => $c['callback_url'],
        'AccountReference'  => substr($accountReference, 0, 12),
        'TransactionDesc'   => substr($description, 0, 13),
    ];

    $ch = curl_init(mpesa_base_url() . '/mpesa/stkpush/v1/processrequest');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . $token]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 25);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'message' => 'Network error contacting M-Pesa: ' . $error];
    }

    $data = json_decode($response, true);

    if (!empty($data['ResponseCode']) && $data['ResponseCode'] === '0') {
        return [
            'success'              => true,
            'checkout_request_id'  => $data['CheckoutRequestID'],
            'merchant_request_id'  => $data['MerchantRequestID'],
            'message'              => 'Prompt sent — ask the customer to check their phone and enter their M-Pesa PIN.',
        ];
    }

    return [
        'success' => false,
        'message' => $data['errorMessage'] ?? ($data['ResponseDescription'] ?? 'M-Pesa declined the request. Please check your Till Number and credentials.'),
    ];
}
