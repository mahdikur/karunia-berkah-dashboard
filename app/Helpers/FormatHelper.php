<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Format number as Indonesian Rupiah
     */
    public static function rupiah($amount, $withPrefix = true): string
    {
        $formatted = number_format((float) $amount, 0, ',', '.');
        return $withPrefix ? 'Rp ' . $formatted : $formatted;
    }

    /**
     * Format date to Indonesian format
     */
    public static function tanggal($date, $format = 'd/m/Y'): string
    {
        if (!$date) return '-';
        return \Carbon\Carbon::parse($date)->format($format);
    }

    /**
     * Format date to long Indonesian format
     */
    public static function tanggalPanjang($date): string
    {
        if (!$date) return '-';
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $d = \Carbon\Carbon::parse($date);
        return $d->day . ' ' . $bulan[$d->month] . ' ' . $d->year;
    }

    /**
     * Format number with Indonesian thousand separator
     */
    public static function angka($number, $decimals = 0): string
    {
        return number_format((float) $number, $decimals, ',', '.');
    }
    public static function terbilang($nilai) {
        $nilai = abs($nilai);
        $huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " ". $huruf[$nilai];
        } else if ($nilai <20) {
            $temp = self::terbilang($nilai - 10). " belas";
        } else if ($nilai < 100) {
            $temp = self::terbilang($nilai/10)." puluh". self::terbilang($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " seratus" . self::terbilang($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = self::terbilang($nilai/100) . " ratus" . self::terbilang($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " seribu" . self::terbilang($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = self::terbilang($nilai/1000) . " ribu" . self::terbilang($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = self::terbilang($nilai/1000000) . " juta" . self::terbilang($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = self::terbilang($nilai/1000000000) . " milyar" . self::terbilang(fmod($nilai,1000000000));
        } else if ($nilai < 1000000000000000) {
            $temp = self::terbilang($nilai/1000000000000) . " trilyun" . self::terbilang(fmod($nilai,1000000000000));
        }
        return $temp;
    }
}
