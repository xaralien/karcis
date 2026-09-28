<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Integrasi Duitku POP (createInvoice) + cek status transaksi.
 * Dokumentasi: https://docs.duitku.com/pop/id/
 */
class Duitku
{
    protected $CI;
    protected $merchant_code;
    protected $api_key;
    protected $sandbox;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->merchant_code = $this->CI->config->item('duitku_merchant_code');
        $this->api_key       = $this->CI->config->item('duitku_api_key');
        $this->sandbox       = (bool) $this->CI->config->item('duitku_sandbox');
    }

    protected function pop_url()
    {
        return $this->sandbox
            ? 'https://api-sandbox.duitku.com/api/merchant/createInvoice'
            : 'https://api-prod.duitku.com/api/merchant/createInvoice';
    }

    protected function status_url()
    {
        return $this->sandbox
            ? 'https://sandbox.duitku.com/webapi/api/merchant/transactionStatus'
            : 'https://passport.duitku.com/webapi/api/merchant/transactionStatus';
    }

    /**
     * Buat invoice. Pembeli memilih metode bayar (VA, QRIS, e-wallet) di halaman Duitku.
     * @return array ['success'=>bool, 'payment_url'=>, 'reference'=>, 'message'=>]
     */
    public function create_invoice(array $order, array $items)
    {
        $timestamp = (string) round(microtime(TRUE) * 1000);
        $signature = hash('sha256', $this->merchant_code . $timestamp . $this->api_key);

        $item_details = array();
        foreach ($items as $it) {
            $item_details[] = array(
                'name'     => mb_substr($it['ticket_name'], 0, 50),
                'price'    => (int) $it['price'] * (int) $it['qty'],
                'quantity' => (int) $it['qty'],
            );
        }
        $item_details[] = array('name' => 'Biaya layanan', 'price' => (int) $order['service_fee'], 'quantity' => (int) $order['ticket_qty']);
        $item_details[] = array('name' => 'Biaya transaksi', 'price' => (int) $order['transaction_fee'], 'quantity' => 1);

        $payload = array(
            'paymentAmount'    => (int) $order['total'],
            'merchantOrderId'  => $order['order_code'],
            'productDetails'   => 'Tiket ' . mb_substr($order['event_title'], 0, 200),
            'additionalParam'  => '',
            'merchantUserInfo' => $order['buyer_email'],
            'customerVaName'   => mb_substr($order['buyer_name'], 0, 20),
            'email'            => $order['buyer_email'],
            'phoneNumber'      => $order['buyer_phone'],
            'itemDetails'      => $item_details,
            'customerDetail'   => array(
                'firstName'   => $order['buyer_name'],
                'lastName'    => '',
                'email'       => $order['buyer_email'],
                'phoneNumber' => $order['buyer_phone'],
            ),
            'callbackUrl'      => $this->CI->fmt->url('payment/callback'),
            'returnUrl'        => $this->CI->fmt->url('payment/status'),
            'expiryPeriod'     => (int) $this->CI->config->item('order_expiry_minutes'),
        );

        $res = $this->request($this->pop_url(), $payload, array(
            'x-duitku-signature: ' . $signature,
            'x-duitku-timestamp: ' . $timestamp,
            'x-duitku-merchantcode: ' . $this->merchant_code,
        ));

        if ($res['http_code'] == 200 && ! empty($res['body']['paymentUrl'])) {
            return array(
                'success'     => TRUE,
                'payment_url' => $res['body']['paymentUrl'],
                'reference'   => isset($res['body']['reference']) ? $res['body']['reference'] : NULL,
                'message'     => 'OK',
            );
        }

        log_message('error', 'Duitku createInvoice gagal: ' . json_encode($res));
        $msg = isset($res['body']['Message']) ? $res['body']['Message']
             : (isset($res['body']['statusMessage']) ? $res['body']['statusMessage'] : 'Tidak dapat terhubung ke Duitku');
        return array('success' => FALSE, 'message' => $msg);
    }

    /** Validasi signature callback: md5(merchantCode + amount + merchantOrderId + apiKey) */
    public function verify_callback(array $post)
    {
        foreach (array('merchantCode', 'amount', 'merchantOrderId', 'signature') as $k) {
            if (empty($post[$k])) return FALSE;
        }
        if ($post['merchantCode'] !== $this->merchant_code) return FALSE;
        $calc = md5($post['merchantCode'] . $post['amount'] . $post['merchantOrderId'] . $this->api_key);
        return hash_equals($calc, $post['signature']);
    }

    /** Cek status langsung ke Duitku. statusCode: 00 sukses, 01 proses, 02 gagal */
    public function check_status($order_code)
    {
        $payload = array(
            'merchantCode'    => $this->merchant_code,
            'merchantOrderId' => $order_code,
            'signature'       => md5($this->merchant_code . $order_code . $this->api_key),
        );
        $res = $this->request($this->status_url(), $payload);
        return $res['http_code'] == 200 ? $res['body'] : NULL;
    }

    protected function request($url, array $payload, array $headers = array())
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST           => TRUE,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => TRUE,
            CURLOPT_HTTPHEADER     => array_merge(array('Content-Type: application/json', 'Accept: application/json'), $headers),
        ));
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        return array('http_code' => $code, 'body' => json_decode((string) $body, TRUE), 'error' => $err);
    }
}
