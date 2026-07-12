<?php
/**
 * AstraCampus - M-Pesa (Safaricom Daraja) Configuration
 *
 * Values are read from Settings (Admin UI → Settings → M-Pesa Integration) by
 * default. For production deployments where you don't want secrets stored in
 * the database, set these environment variables instead — they always win:
 *
 *   ASTRA_MPESA_ENV               "sandbox" or "live"
 *   ASTRA_MPESA_CONSUMER_KEY
 *   ASTRA_MPESA_CONSUMER_SECRET
 *   ASTRA_MPESA_SHORTCODE         Your Till Number (Buy Goods) or Paybill number
 *   ASTRA_MPESA_PASSKEY
 *   ASTRA_MPESA_CALLBACK_URL      Public HTTPS URL Safaricom will POST results to
 *                                 e.g. https://yourschool.com/astracampus/public/mpesa/callback
 */

function mpesa_config(): array
{
    return [
        'environment'      => getenv('ASTRA_MPESA_ENV') ?: get_setting('mpesa_environment', 'sandbox'),
        'consumer_key'     => getenv('ASTRA_MPESA_CONSUMER_KEY') ?: get_setting('mpesa_consumer_key', ''),
        'consumer_secret'  => getenv('ASTRA_MPESA_CONSUMER_SECRET') ?: get_setting('mpesa_consumer_secret', ''),
        'shortcode'        => getenv('ASTRA_MPESA_SHORTCODE') ?: get_setting('mpesa_till_number', ''),
        'passkey'          => getenv('ASTRA_MPESA_PASSKEY') ?: get_setting('mpesa_passkey', ''),
        'callback_url'     => getenv('ASTRA_MPESA_CALLBACK_URL') ?: get_setting('mpesa_callback_url', ''),
        // Buy Goods till numbers use CustomerBuyGoodsOnline; Paybill numbers use CustomerPayBillOnline.
        'transaction_type' => get_setting('mpesa_transaction_type', 'CustomerBuyGoodsOnline'),
    ];
}

function mpesa_is_configured(): bool
{
    $c = mpesa_config();
    return $c['consumer_key'] !== '' && $c['consumer_secret'] !== '' && $c['shortcode'] !== ''
        && $c['passkey'] !== '' && $c['callback_url'] !== '';
}

function mpesa_base_url(): string
{
    return mpesa_config()['environment'] === 'live'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke';
}
