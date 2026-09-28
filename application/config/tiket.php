<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Aturan harga Karcis
| Biaya layanan dikenakan per tiket, biaya transaksi sekali per transaksi.
|   1 tiket : (1 x 2.000) + 4.000 = 6.000 biaya
|   4 tiket : (4 x 2.000) + 4.000 = 12.000 biaya
*/
$config['app_name']                = 'Karcis';
$config['service_fee_per_ticket']  = 2000;
$config['transaction_fee']         = 4000;
$config['max_tickets_per_order']   = 4;
$config['order_expiry_minutes']    = 60;   // batas waktu bayar
$config['email_dns_check']         = getenv('EMAIL_DNS_CHECK') !== '0'; // cek domain email benar-benar ada
$config['support_email']           = 'halo@karcis.id';
$config['mail_from']               = getenv('SMTP_USER') ?: 'emailkamu@gmail.com';
$config['mail_from_name']          = 'Karcis';
