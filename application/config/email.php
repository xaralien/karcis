<?php
defined('BASEPATH') or exit('No direct script access allowed');

// Isi dengan SMTP milikmu (Gmail App Password, Mailtrap, SES, dll)
$config['protocol']    = getenv('MAIL_PROTOCOL') ?: 'smtp';
$config['mailpath']    = getenv('MAIL_PATH') ?: '/usr/sbin/sendmail';
$config['smtp_timeout'] = 10;
$config['smtp_host']   = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
$config['smtp_user']   = getenv('SMTP_USER') ?: 'dimasuciha126@gmail.com';
$config['smtp_pass']   = getenv('SMTP_PASS') ?: 'bgvx wjii nget hlmc';
$config['smtp_port']   = 587;
$config['smtp_crypto'] = 'tls';
$config['mailtype']    = 'html';
$config['charset']     = 'utf-8';
$config['newline']     = "\r\n";
$config['crlf']        = "\r\n";
