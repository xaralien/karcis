<?php defined('BASEPATH') OR exit('No direct script access allowed'); /* $meta, $auto */ ?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex">
  <title><?= html_escape($meta['title']) ?> — <?= html_escape($meta['app_name']) ?></title>
  <style>
    :root { --ink:#1E1540; --muted:#6F6890; --line:#DCD6EC; --plum:#5B3FD9; --mint:#12966F; --danger:#C8323B; --paper:#EDEAF5; }
    * { box-sizing: border-box; }
    body { margin:0; background: var(--paper); color: var(--ink); font-family: "Segoe UI", Arial, sans-serif; font-size: 12px; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .bar { position: sticky; top:0; background: var(--ink); color:#fff; padding:12px 18px; display:flex; gap:10px; align-items:center; }
    .bar a, .bar button { font: inherit; font-weight:700; border:0; border-radius:99px; padding:8px 16px; cursor:pointer; text-decoration:none; }
    .bar .back { background: rgba(255,255,255,.14); color:#fff; }
    .bar .print { background:#FFB800; color: var(--ink); margin-left:auto; }
    .sheet { background:#fff; width: 297mm; min-height: 210mm; margin: 18px auto; padding: 14mm; box-shadow: 0 4px 22px rgba(30,21,64,.12); }
    h1 { font-size: 20px; margin: 0 0 2px; }
    .meta { color: var(--muted); font-size: 11px; margin-bottom: 14px; }
    .cards { display:flex; gap:10px; flex-wrap:wrap; margin-bottom: 14px; }
    .card { border:1px solid var(--line); border-radius:8px; padding:8px 12px; min-width: 120px; }
    .card .k { font-size:10px; color: var(--muted); }
    .card .v { font-size:16px; font-weight:800; }
    table { width:100%; border-collapse: collapse; }
    th { background:#F3F0FB; text-align:left; font-size:10.5px; padding:6px; border-bottom:1.5px solid var(--line); }
    td { padding:5px 6px; border-bottom:1px solid #EFECF8; }
    td.num, th.num { text-align:right; white-space:nowrap; }
    tfoot td { font-weight:800; border-top:1.5px solid var(--line); background:#FAF9FE; }
    .tag { font-weight:700; font-size:10px; padding:2px 7px; border-radius:99px; }
    .ok { background:#E3F6EF; color: var(--mint); }
    .no { background:#FCE8E9; color: var(--danger); }
    .foot { margin-top: 10mm; font-size:10px; color: var(--muted); display:flex; justify-content:space-between; }
    @page { size: A4 landscape; margin: 10mm; }
    @media print { body { background:#fff; } .bar { display:none; } .sheet { width:auto; margin:0; padding:0; box-shadow:none; min-height:0; } thead { display: table-header-group; } tr { break-inside: avoid; } }
  </style>
</head>
<body>
<div class="bar">
  <a class="back" href="<?= $this->fmt->url('admin/reports') ?>">&larr; Kembali</a>
  <span style="color:rgba(255,255,255,.7)">Pilih “Simpan sebagai PDF” pada tujuan cetak</span>
  <button class="print" type="button" onclick="window.print()">Cetak / Simpan PDF</button>
</div>
<div class="sheet">
  <h1><?= html_escape($meta['app_name']) ?> — <?= html_escape($meta['title']) ?></h1>
  <div class="meta">
    Event: <strong><?= html_escape($meta['event']) ?></strong> ·
    Periode: <?= html_escape($meta['periode']) ?> ·
    Dibuat <?= html_escape($meta['dicetak']) ?> WIB oleh <?= html_escape($meta['oleh']) ?>
  </div>
