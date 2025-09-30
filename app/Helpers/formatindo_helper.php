<?php
// app/Helpers/formatindo_helper.php

use CodeIgniter\I18n\Time;

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
                // asumsikan timestamp detik
                return Time::createFromTimestamp((int) $value, $tz);
            }
            return Time::parse((string) $value, $tz);
        } catch (\Throwable $e) {
            return null;
        }
    }
}

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
        $long  = [1 => 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $shorts = [1 => 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
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
            $m = (int) $t->getMonth();
        }

        $long  = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $shorts = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        if ($m < 1 || $m > 12) return '—';
        return $short ? $shorts[$m] : $long[$m];
    }
}

if (! function_exists('tgl_indo')) {
    /**
     * Format tanggal Indonesia (tanpa jam).
     * Contoh: tgl_indo('2025-09-28', true) => "Minggu, 28 September 2025"
     */
    function tgl_indo($date, bool $withDay = false, bool $shortMonth = false, string $tz = 'Asia/Jakarta'): string
    {
        $t = _indo_parse_time($date, $tz);
        if (! $t) return '—';

        $d = (int) $t->format('j');
        $y = (int) $t->format('Y');
        $bulan = bulan_indo($t, $shortMonth, $tz);
        $base = $d . ' ' . $bulan . ' ' . $y;

        return $withDay ? (hari_indo($t, false, $tz) . ', ' . $base) : $base;
    }
}

if (! function_exists('dt_indo')) {
    /**
     * Format tanggal & waktu Indonesia.
     * Contoh:
     *  dt_indo('2025-09-28 14:05:33', true) => "Minggu, 28 September 2025 14:05"
     *  dt_indo(..., true, true)             => "Minggu, 28 September 2025 14:05:33"
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

        $date = tgl_indo($t, $withDay, false, $tz);
        $time = $withSeconds ? $t->format('H:i:s') : $t->format('H:i');
        $suffix = $withTZSuffix ? ' WIB' : '';

        return $date . ' ' . $time . $suffix;
    }
}

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
                $val = floor($sec / $s);
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

        $now = new DateTimeImmutable('now', new DateTimeZone($tz));
        $bd  = new DateTimeImmutable($t->toDateTimeString(), new DateTimeZone($tz));
        $diff = $now->diff($bd);

        $parts = [];
        if ($diff->y) $parts[] = $diff->y . ' tahun';
        if ($diff->m) $parts[] = $diff->m . ' bulan';
        if ($diff->d) $parts[] = $diff->d . ' hari';
        return $parts ? implode(' ', $parts) : '0 hari';
    }
}
