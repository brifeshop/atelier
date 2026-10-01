<?php

if (!function_exists('format_rupiah')) {
    function format_rupiah($value, $withSymbol = true)
    {
        $formatted = number_format($value, 0, ',', '.');
        return $withSymbol ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('format_rupiah_short')) {
    function format_rupiah_short($value)
    {
        if ($value >= 1000000000) {
            return 'Rp ' . number_format($value / 1000000000, 1, ',', '.') . 'M';
        }
        if ($value >= 1000000) {
            return 'Rp ' . number_format($value / 1000000, 1, ',', '.') . 'jt';
        }
        if ($value >= 1000) {
            return 'Rp ' . number_format($value / 1000, 0, ',', '.') . 'rb';
        }
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}

if (!function_exists('format_angka')) {
    function format_angka($value, $decimals = 0)
    {
        return number_format($value, $decimals, ',', '.');
    }
}

if (!function_exists('format_tanggal')) {
    function format_tanggal($date, $format = 'd M Y')
    {
        if (!$date) return '-';
        return \Carbon\Carbon::parse($date)->translatedFormat($format);
    }
}

if (!function_exists('format_tanggal_waktu')) {
    function format_tanggal_waktu($date)
    {
        if (!$date) return '-';
        return \Carbon\Carbon::parse($date)->translatedFormat('d M Y, H:i');
    }
}

if (!function_exists('generate_kode')) {
    function generate_kode($prefix, $model, $field = 'kode', $padLength = 4)
    {
        $lastRecord = $model::orderBy('id', 'desc')->first();
        $lastNumber = $lastRecord ? (int) substr($lastRecord->$field, -$padLength) : 0;
        $newNumber = $lastNumber + 1;
        
        return $prefix . '-' . str_pad($newNumber, $padLength, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('status_badge')) {
    function status_badge($status)
    {
        $badges = [
            'draft' => 'bg-navy-800 text-navy-300 border-navy-700',
            'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
            'approved' => 'bg-green-500/10 text-green-400 border-green-500/30',
            'confirmed' => 'bg-green-500/10 text-green-400 border-green-500/30',
            'in_progress' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
            'completed' => 'bg-green-500/10 text-green-400 border-green-500/30',
            'cancelled' => 'bg-red-500/10 text-red-400 border-red-500/30',
            'rejected' => 'bg-red-500/10 text-red-400 border-red-500/30',
        ];

        return $badges[strtolower($status)] ?? 'bg-navy-800 text-navy-300 border-navy-700';
    }
}