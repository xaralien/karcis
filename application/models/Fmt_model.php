<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pengganti file helper: semua fungsi bantu tampilan & perhitungan ada di sini.
 * Dipanggil dari view maupun controller dengan $this->fmt->...
 */
class Fmt_model extends CI_Model
{
    /* ---------------- URL ---------------- */
    public function base($path = '')
    {
        return rtrim($this->config->base_url(), '/') . '/' . ltrim($path, '/');
    }

    /**
     * URL file CSS/JS dengan penanda versi (?v=waktu-ubah-file).
     * Setiap kali file diganti, alamatnya ikut berubah sehingga browser
     * dan CDN otomatis mengambil versi terbaru, tidak memakai cache lama.
     */
    public function asset($path)
    {
        $file = FCPATH . ltrim($path, '/');
        $v    = is_file($file) ? filemtime($file) : time();
        return $this->base($path) . '?v=' . $v;
    }

    /** URL halaman, mengikuti pola controller/method bawaan CodeIgniter */
    public function url($uri = '')
    {
        return $this->config->site_url($uri);
    }

    /** Pindah halaman (pengganti redirect() dari url helper) */
    public function go($uri = '')
    {
        $to = preg_match('#^https?://#i', $uri) ? $uri : $this->url($uri);
        header('Location: ' . $to, TRUE, 302);
        exit;
    }

    public function slugify($text)
    {
        $text = strtolower(trim(strip_tags((string) $text)));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }

    /* ---------------- Form ---------------- */
    /** Input tersembunyi CSRF (pengganti form_open) */
    public function csrf()
    {
        return '<input type="hidden" name="' . $this->security->get_csrf_token_name()
             . '" value="' . $this->security->get_csrf_hash() . '">';
    }

    /** Nilai lama setelah validasi gagal (pengganti set_value) */
    public function old($field, $default = '')
    {
        $v = $this->input->post($field);
        return html_escape($v === NULL ? $default : $v);
    }

    /** Pengganti set_checkbox */
    public function checked($field, $value = '1', $default = FALSE)
    {
        if ($this->input->method() === 'post') {
            return (string) $this->input->post($field) === (string) $value ? 'checked' : '';
        }
        return $default ? 'checked' : '';
    }

    /** Pesan error validasi satu kolom (pengganti form_error) */
    public function err($field, $extra = array())
    {
        $msg = isset($extra[$field]) ? $extra[$field] : $this->form_validation->error($field);
        return $msg ? '<div class="invalid-feedback d-block">' . html_escape(strip_tags($msg)) . '</div>' : '';
    }

    public function has_errors($extra = array())
    {
        return $extra || strip_tags($this->form_validation->error_string()) !== '';
    }

    /* ---------------- Angka & tanggal ---------------- */
    public function rupiah($angka)
    {
        return 'Rp' . number_format((int) $angka, 0, ',', '.');
    }

    public function angka($n)
    {
        return number_format((int) $n, 0, ',', '.');
    }

    public function tgl($date, $with_day = TRUE)
    {
        $hari  = array('Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu');
        $bulan = array(1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember');
        $ts  = strtotime($date);
        $str = date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
        return $with_day ? $hari[(int) date('w', $ts)] . ', ' . $str : $str;
    }

    public function tgl_pendek($date)
    {
        $bulan = array(1=>'Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des');
        $ts = strtotime($date);
        return array('d' => date('d', $ts), 'm' => $bulan[(int) date('n', $ts)], 'y' => date('Y', $ts));
    }

    public function jam($time)
    {
        return date('H.i', strtotime($time));
    }

    public function waktu($datetime)
    {
        return date('d/m/Y H.i', strtotime($datetime));
    }

    /* ---------------- Gambar ---------------- */
    /** Gambar bisa URL penuh atau hasil upload (uploads/events/xxx.jpg) */
    public function img($path)
    {
        if ( ! $path) return $this->base('assets/img/placeholder.svg');
        return preg_match('#^https?://#i', $path) ? $path : $this->base($path);
    }

    /* ---------------- Biaya ---------------- */
    /**
     * Biaya layanan 2.000 per tiket + biaya transaksi 4.000 per transaksi.
     * $items: array of array('price' => int, 'qty' => int)
     */
    public function fees(array $items)
    {
        $qty = 0; $subtotal = 0;
        foreach ($items as $it) {
            $qty      += (int) $it['qty'];
            $subtotal += (int) $it['price'] * (int) $it['qty'];
        }
        $service     = $qty * (int) $this->config->item('service_fee_per_ticket');
        $transaction = $qty > 0 ? (int) $this->config->item('transaction_fee') : 0;

        return array(
            'qty'             => $qty,
            'subtotal'        => $subtotal,
            'service_fee'     => $service,
            'transaction_fee' => $transaction,
            'total'           => $subtotal + $service + $transaction,
        );
    }

    public function service_fee()     { return (int) $this->config->item('service_fee_per_ticket'); }
    public function transaction_fee() { return (int) $this->config->item('transaction_fee'); }
    public function max_tickets()     { return (int) $this->config->item('max_tickets_per_order'); }

    /* ---------------- Email ---------------- */
    /** Domain email harus benar-benar ada, supaya tiket tidak nyasar */
    public function email_valid($email)
    {
        $email = trim((string) $email);
        if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) return FALSE;
        if ( ! $this->config->item('email_dns_check') || ! function_exists('checkdnsrr')) return TRUE;
        $domain = substr(strrchr($email, '@'), 1);
        return checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A');
    }

    /** Saran perbaikan domain yang umum salah ketik */
    public function email_typo($email)
    {
        $typos = array(
            'gmial.com'=>'gmail.com','gmai.com'=>'gmail.com','gmail.co'=>'gmail.com','gamil.com'=>'gmail.com',
            'gnail.com'=>'gmail.com','gmail.con'=>'gmail.com','yahoo.co'=>'yahoo.com','yaho.com'=>'yahoo.com',
            'yahoo.con'=>'yahoo.com','hotmial.com'=>'hotmail.com','outlok.com'=>'outlook.com','outlook.co'=>'outlook.com',
        );
        $parts = explode('@', strtolower(trim((string) $email)));
        return (count($parts) === 2 && isset($typos[$parts[1]])) ? $parts[0] . '@' . $typos[$parts[1]] : NULL;
    }

    /* ---------------- Pagination ---------------- */
    /** Data halaman untuk dirender view (pengganti library pagination) */
    public function pages($total, $per_page, $current, $base_query = array())
    {
        $last = max(1, (int) ceil($total / $per_page));
        $cur  = min(max(1, (int) $current), $last);
        $from = max(1, $cur - 2);
        $to   = min($last, $from + 4);
        $from = max(1, $to - 4);

        $links = array();
        for ($i = $from; $i <= $to; $i++) {
            $q = array_filter(array_merge($base_query, array('page' => $i)));
            $links[] = array('no' => $i, 'url' => '?' . http_build_query($q), 'active' => $i === $cur);
        }
        $q_prev = array_filter(array_merge($base_query, array('page' => $cur - 1)));
        $q_next = array_filter(array_merge($base_query, array('page' => $cur + 1)));

        return array(
            'current' => $cur, 'last' => $last, 'total' => (int) $total, 'links' => $links,
            'prev' => $cur > 1 ? '?' . http_build_query($q_prev) : NULL,
            'next' => $cur < $last ? '?' . http_build_query($q_next) : NULL,
        );
    }
}
