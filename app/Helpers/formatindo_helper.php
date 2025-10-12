<?php
// app/Helpers/formatindo_helper.php

use CodeIgniter\I18n\Time;

/**
 * =========================
 *  Parser Waktu Indonesia
 * =========================
 */
if (! function_exists('_indo_parse_time')) {
    /**
     * Parse berbagai input (string/timestamp/DateTime/Time) ke CI Time
     */
    function _indo_parse_time($value, string $tz = 'Asia/Jakarta'): ?Time
    {
        if ($value === null || $value === '' || $value === '0000-00-00' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        try {
            if ($value instanceof Time) {
                return $value->setTimezone($tz);
            }
            if ($value instanceof \DateTimeInterface) {
                return Time::instance($value)->setTimezone($tz);
            }
            if (is_numeric($value)) {
                // Asumsikan timestamp (detik)
                return Time::createFromTimestamp((int) $value, $tz);
            }
            // String datetime/tanggal
            return Time::parse((string) $value, $tz);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

/**
 * =========================
 *  Util Nama Hari/Bulan
 * =========================
 */
if (! function_exists('hari_indo')) {
    /**
     * Nama hari Indonesia.
     * @param mixed $date    Tanggal/waktu
     * @param bool  $short   true = Sen, Rab; false = Senin, Rabu
     */
    function hari_indo($date, bool $short = false, string $tz = 'Asia/Jakarta'): string
    {
        $t = _indo_parse_time($date, $tz);
        if (! $t) return '—';

        // 1 (Senin) .. 7 (Minggu)
        $n = (int) $t->format('N');
        $long   = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $shorts = [1 => 'Sen',   'Sel',    'Rab',  'Kam',   'Jum',   'Sab',   'Min'];
        return $short ? $shorts[$n] : $long[$n];
    }
}

if (! function_exists('bulan_indo')) {
    /**
     * Nama bulan Indonesia dari tanggal atau angka 1..12
     */
    function bulan_indo($monthOrDate, bool $short = false, string $tz = 'Asia/Jakarta'): string
    {
        if (is_numeric($monthOrDate)) {
            $m = (int) $monthOrDate;
        } else {
            $t = _indo_parse_time($monthOrDate, $tz);
            if (! $t) return '—';
            $m = (int) $t->format('n');
        }

        $long   = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $shorts = [1 => 'Jan',     'Feb',      'Mar',   'Apr',   'Mei', 'Jun',  'Jul',  'Agu',     'Sep',       'Okt',     'Nov',      'Des'];
        if ($m < 1 || $m > 12) return '—';
        return $short ? $shorts[$m] : $long[$m];
    }
}

/**
 * =========================
 *  Formatter Umum
 * =========================
 */
if (! function_exists('tgl_indo')) {
    /**
     * Format tanggal Indonesia (tanpa jam).
     * Contoh: tgl_indo('2025-10-07', true) => "Selasa, 7 Oktober 2025"
     */
    function tgl_indo($date, bool $withDay = false, bool $shortMonth = false, string $tz = 'Asia/Jakarta'): string
    {
        $t = _indo_parse_time($date, $tz);
        if (! $t) return '—';

        $d = (int) $t->format('j');
        $y = (int) $t->format('Y');
        $bulan = bulan_indo($t, $shortMonth, $tz);
        $base  = $d . ' ' . $bulan . ' ' . $y;

        return $withDay ? (hari_indo($t, false, $tz) . ', ' . $base) : $base;
    }
}

if (! function_exists('dt_indo')) {
    /**
     * Format tanggal & waktu Indonesia.
     * Contoh:
     *  dt_indo('2025-10-07 14:05:33', true) => "Selasa, 7 Oktober 2025 14:05"
     */
    function dt_indo(
        $datetime,
        bool $withDay = true,
        bool $withSeconds = false,
        string $tz = 'Asia/Jakarta',
        bool $withTZSuffix = false // contoh: WIB
    ): string {
        $t = _indo_parse_time($datetime, $tz);
        if (! $t) return '—';

        $date   = tgl_indo($t, $withDay, false, $tz);
        $time   = $withSeconds ? $t->format('H:i:s') : $t->format('H:i');
        $suffix = $withTZSuffix ? ' WIB' : '';

        return $date . ' ' . $time . $suffix;
    }
}

/**
 * =========================
 *  Humanize & Umur
 * =========================
 */
if (! function_exists('sejak')) {
    /**
     * Humanize selisih waktu (time-ago) dalam bahasa Indonesia sederhana.
     * Contoh: "3 menit lalu", "2 hari lagi" (jika di masa depan)
     */
    function sejak($datetime, string $tz = 'Asia/Jakarta'): string
    {
        $t = _indo_parse_time($datetime, $tz);
        if (! $t) return '—';

        $now   = Time::now($tz);
        $diff  = $now->getTimestamp() - $t->getTimestamp();
        $ahead = $diff < 0;
        $sec   = abs($diff);

        $units = [
            31536000 => 'tahun',
            2592000  => 'bulan',
            604800   => 'minggu',
            86400    => 'hari',
            3600     => 'jam',
            60       => 'menit',
            1        => 'detik',
        ];

        foreach ($units as $s => $label) {
            if ($sec >= $s) {
                $val = (int) floor($sec / $s);
                return $ahead ? "{$val} {$label} lagi" : "{$val} {$label} lalu";
            }
        }
        return 'baru saja';
    }
}

if (! function_exists('umur')) {
    /**
     * Hitung umur dari tanggal lahir.
     * Hasil: "X tahun Y bulan Z hari"
     */
    function umur($birthdate, string $tz = 'Asia/Jakarta'): string
    {
        $t = _indo_parse_time($birthdate, $tz);
        if (! $t) return '—';

        $now  = new \DateTimeImmutable('now', new \DateTimeZone($tz));
        $bd   = new \DateTimeImmutable($t->toDateTimeString(), new \DateTimeZone($tz));
        $diff = $now->diff($bd);

        $parts = [];
        if ($diff->y) $parts[] = $diff->y . ' tahun';
        if ($diff->m) $parts[] = $diff->m . ' bulan';
        if ($diff->d) $parts[] = $diff->d . ' hari';
        return $parts ? implode(' ', $parts) : '0 hari';
    }
}

/**
 * ===========================================
 *  KHUSUS: Input "dd/mm/YYYY" → output Indo
 * ===========================================
 * Berguna saat data datang dari form/Excel seperti "07/10/2025"
 */
if (! function_exists('format_ddmmyyyy_ke_tanggal_indo')) {
    /**
     * "07/10/2025" -> "07 Oktober 2025"
     */
    function format_ddmmyyyy_ke_tanggal_indo(string $ddmmyyyy): string
    {
        $ddmmyyyy = trim($ddmmyyyy);
        if ($ddmmyyyy === '') return '—';

        $parts = explode('/', $ddmmyyyy);
        if (count($parts) !== 3) return '—';

        [$d, $m, $y] = $parts;
        $d = (int) $d;
        $m = (int) $m;
        $y = (int) $y;

        if ($d < 1 || $d > 31 || $m < 1 || $m > 12 || $y < 1) return '—';

        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return sprintf('%02d %s %04d', $d, $bulan[$m], $y);
    }
}

if (! function_exists('format_ddmmyyyy_ke_bulan_tahun')) {
    /**
     * "07/10/2025" -> "Oktober 2025"
     */
    function format_ddmmyyyy_ke_bulan_tahun(string $ddmmyyyy): string
    {
        $ddmmyyyy = trim($ddmmyyyy);
        if ($ddmmyyyy === '') return '—';

        $parts = explode('/', $ddmmyyyy);
        if (count($parts) !== 3) return '—';

        [$d, $m, $y] = $parts;
        $m = (int) $m;
        $y = (int) $y;

        if ($m < 1 || $m > 12 || $y < 1) return '—';

        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        return $bulan[$m] . ' ' . $y;
    }
}

/**
 * ===========================================
 *  Khusus: Format "Hari, MM-YYYY" / "Hari, Bulan YYYY"
 *  (seperti fungsi kamu sebelumnya, tapi aman)
 * ===========================================
 */
if (! function_exists('indo_format_hari_bulan_tahun')) {
    /**
     * Format ke: "Rabu, 10-2025" (Nama hari Indonesia, lalu MM-YYYY numerik)
     */
    function indo_format_hari_bulan_tahun($value, string $tz = 'Asia/Jakarta'): ?string
    {
        $t = _indo_parse_time($value, $tz);
        if (! $t) return null;

        $dow = (int) $t->format('N');   // 1..7
        $hari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];

        $month = (int) $t->format('n'); // 1..12
        $year  = (int) $t->format('Y');

        return sprintf('%s, %02d-%04d', $hari[$dow] ?? '', $month, $year);
    }
}

if (! function_exists('indo_format_hari_bulanNama_tahun')) {
    /**
     * Alternatif: "Rabu, Oktober 2025" (bulan huruf)
     */
    function indo_format_hari_bulanNama_tahun($value, string $tz = 'Asia/Jakarta'): ?string
    {
        $t = _indo_parse_time($value, $tz);
        if (! $t) return null;

        $dow = (int) $t->format('N');
        $hari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        $m = (int) $t->format('n');
        $y = (int) $t->format('Y');

        return sprintf('%s, %s %d', $hari[$dow] ?? '', $bulan[$m] ?? '', $y);
    }
}
