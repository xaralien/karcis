<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Laporan & ekspor:
 *  - buyers     : data pembeli untuk remarketing
 *  - audit      : audit penjualan & biaya
 *  - attendance : laporan akhir kehadiran (tiket yang sudah & belum dipindai)
 *
 * Format: xls (dibuka Excel/Spreadsheet), csv, dan pdf (halaman cetak → Simpan sebagai PDF).
 */
class Reports extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Report_model');
    }

    public function index()
    {
        $f = $this->filter();
        $this->render('reports/index', array(
            'title'      => 'Laporan & Ekspor',
            'filter'     => $f,
            'events'     => $this->Admin_model->events(),
            'summary'    => $this->Report_model->audit_summary($f),
            'attendance' => $this->Report_model->attendance_summary($f),
            'buyers'     => count($this->Report_model->buyers($f)),
            'nav'        => 'reports',
        ));
    }

    /* ---------------- 1. Data pembeli ---------------- */
    public function buyers($format = 'xls')
    {
        $f    = $this->filter();
        $rows = $this->Report_model->buyers($f);

        $head = array('Email', 'Nama', 'Nomor HP', 'Punya akun', 'Jumlah pesanan', 'Jumlah tiket',
                      'Total belanja', 'Tiket terpakai', 'Pembelian pertama', 'Pembelian terakhir',
                      'Event yang dibeli', 'Kategori', 'Kota');
        $data = array();
        foreach ($rows as $r) {
            $data[] = array(
                $r->buyer_email, $r->buyer_name, $r->buyer_phone, $r->has_account ? 'Ya' : 'Tidak',
                (int) $r->orders, (int) $r->tickets, (int) $r->spent, (int) $r->attended,
                $r->first_order ? date('d/m/Y', strtotime($r->first_order)) : '-',
                $r->last_order ? date('d/m/Y', strtotime($r->last_order)) : '-',
                $r->events, $r->categories, $r->cities,
            );
        }
        $title = 'Data Pembeli';
        $this->deliver($format, 'data-pembeli', $title, $head, $data, array(
            'view'    => 'reports/print_buyers',
            'vars'    => array('rows' => $rows),
            'numeric' => array(4, 5, 6, 7),
            'money'   => array(6),
        ), $f);
    }

    /* ---------------- 2. Audit penjualan ---------------- */
    public function audit($format = 'xls')
    {
        $f     = $this->filter();
        $rows  = $this->Report_model->audit_orders($f);
        $label = array('paid' => 'Lunas', 'pending' => 'Menunggu', 'failed' => 'Gagal', 'expired' => 'Kedaluwarsa');

        $head = array('Kode pesanan', 'Tanggal dibuat', 'Tanggal bayar', 'Event', 'Nama pembeli', 'Email',
                      'Jumlah tiket', 'Harga tiket', 'Biaya layanan', 'Biaya transaksi', 'Total',
                      'Status', 'Referensi Duitku', 'Metode');
        $data = array();
        foreach ($rows as $o) {
            $data[] = array(
                $o->order_code, date('d/m/Y H:i', strtotime($o->created_at)),
                $o->paid_at ? date('d/m/Y H:i', strtotime($o->paid_at)) : '-',
                $o->event_title, $o->buyer_name, $o->buyer_email, (int) $o->ticket_qty,
                (int) $o->subtotal, (int) $o->service_fee, (int) $o->transaction_fee, (int) $o->total,
                $label[$o->status], $o->duitku_reference, $o->payment_code,
            );
        }
        $this->deliver($format, 'audit-penjualan', 'Audit Penjualan', $head, $data, array(
            'view'    => 'reports/print_audit',
            'vars'    => array('rows' => $rows, 'summary' => $this->Report_model->audit_summary($f),
                               'per_type' => $this->Report_model->audit_per_type($f), 'label' => $label),
            'numeric' => array(6, 7, 8, 9, 10),
            'money'   => array(7, 8, 9, 10),
        ), $f);
    }

    /* ---------------- 3. Data kehadiran ---------------- */
    public function attendance($format = 'xls')
    {
        $f    = $this->filter();
        $rows = $this->Report_model->attendance($f);

        $head = array('Kode tiket', 'Kategori tiket', 'Event', 'Kode pesanan', 'Nama pembeli', 'Email',
                      'Nomor HP', 'Status', 'Waktu masuk', 'Petugas');
        $data = array();
        foreach ($rows as $t) {
            $data[] = array(
                $t->ticket_code, $t->ticket_name, $t->event_title, $t->order_code, $t->buyer_name,
                $t->buyer_email, $t->buyer_phone,
                $t->is_checked_in ? 'Sudah scan' : 'Belum scan',
                $t->checked_in_at ? date('d/m/Y H:i', strtotime($t->checked_in_at)) : '-',
                $t->petugas ?: '-',
            );
        }
        $this->deliver($format, 'kehadiran', 'Data Kehadiran', $head, $data, array(
            'view' => 'reports/print_attendance',
            'vars' => array('rows' => $rows, 'summary' => $this->Report_model->attendance_summary($f),
                            'per_type' => $this->Report_model->attendance_per_type($f),
                            'by_hour' => $this->Report_model->attendance_by_hour($f)),
        ), $f);
    }

    /* ---------------- Util ---------------- */
    protected function filter()
    {
        return array(
            'event_id' => (int) $this->input->get('event_id'),
            'from'     => (string) $this->input->get('from', TRUE),
            'to'       => (string) $this->input->get('to', TRUE),
            'status'   => (string) $this->input->get('status', TRUE),
            'hadir'    => $this->input->get('hadir') === NULL ? '' : (string) $this->input->get('hadir', TRUE),
        );
    }

    protected function event_name($f)
    {
        if (empty($f['event_id'])) return 'Semua event';
        $e = $this->Admin_model->event($f['event_id']);
        return $e ? $e->title : 'Event tidak ditemukan';
    }

    protected function periode($f)
    {
        if ($f['from'] && $f['to']) return $this->fmt->tgl($f['from'], FALSE) . ' – ' . $this->fmt->tgl($f['to'], FALSE);
        if ($f['from']) return 'Sejak ' . $this->fmt->tgl($f['from'], FALSE);
        if ($f['to'])   return 'Sampai ' . $this->fmt->tgl($f['to'], FALSE);
        return 'Semua periode';
    }

    /** Kirim hasil sesuai format yang diminta */
    protected function deliver($format, $slug, $title, array $head, array $data, array $print, array $f)
    {
        $meta = array(
            'title'    => $title,
            'event'    => $this->event_name($f),
            'periode'  => $this->periode($f),
            'dicetak'  => date('d/m/Y H:i'),
            'oleh'     => $this->admin->name,
            'app_name' => $this->config->item('app_name'),
            'filter'   => $f,
        );
        $file = $slug . '-' . date('Ymd-His');

        if ($format === 'csv')  return $this->send_csv($file, $head, $data);
        if ($format === 'xls')  return $this->send_xls($file, $meta, $head, $data, $print);
        // pdf: halaman siap cetak, pembaca memilih "Simpan sebagai PDF" di dialog cetak
        return $this->load->view('admin/' . $print['view'], array_merge($print['vars'], array(
            'meta' => $meta, 'auto' => (bool) $this->input->get('auto'),
        )));
    }

    protected function send_csv($file, array $head, array $data)
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, $head);
        foreach ($data as $row) fputcsv($out, $row);
        rewind($out);
        $csv = "\xEF\xBB\xBF" . stream_get_contents($out);
        fclose($out);

        $this->output->set_content_type('text/csv', 'utf-8')
            ->set_header('Content-Disposition: attachment; filename="' . $file . '.csv"')
            ->set_output($csv);
    }

    /**
     * .xls dalam bentuk tabel HTML: dikenali Excel, LibreOffice, dan Google Sheets,
     * tanpa perlu library tambahan.
     */
    protected function send_xls($file, array $meta, array $head, array $data, array $print)
    {
        $numeric = isset($print['numeric']) ? $print['numeric'] : array();

        $h  = '<html xmlns:x="urn:schemas-microsoft-com:office:excel"><head><meta charset="utf-8">';
        $h .= '<style>table{border-collapse:collapse}th,td{border:1px solid #999;padding:4px 6px;font-family:Calibri,Arial;font-size:11pt}'
            . 'th{background:#1E1540;color:#fff;font-weight:bold}.hd{font-size:14pt;font-weight:bold;border:0}.sub{border:0;color:#555}</style></head><body>';
        $h .= '<table><tr><td class="hd" colspan="' . count($head) . '">' . html_escape($meta['app_name'] . ' — ' . $meta['title']) . '</td></tr>';
        $h .= '<tr><td class="sub" colspan="' . count($head) . '">Event: ' . html_escape($meta['event'])
            . ' | Periode: ' . html_escape($meta['periode'])
            . ' | Dibuat: ' . html_escape($meta['dicetak']) . ' oleh ' . html_escape($meta['oleh']) . '</td></tr>';
        $h .= '<tr><td class="sub" colspan="' . count($head) . '"></td></tr><tr>';
        foreach ($head as $c) $h .= '<th>' . html_escape($c) . '</th>';
        $h .= '</tr>';

        foreach ($data as $row) {
            $h .= '<tr>';
            foreach (array_values($row) as $i => $cell) {
                $num = in_array($i, $numeric, TRUE) && is_numeric($cell);
                $h .= '<td' . ($num ? ' style="mso-number-format:\'#,##0\'"' : ' style="mso-number-format:\'@\'"') . '>'
                    . html_escape((string) $cell) . '</td>';
            }
            $h .= '</tr>';
        }
        $h .= '</table></body></html>';

        $this->output->set_content_type('application/vnd.ms-excel', 'utf-8')
            ->set_header('Content-Disposition: attachment; filename="' . $file . '.xls"')
            ->set_output($h);
    }
}
