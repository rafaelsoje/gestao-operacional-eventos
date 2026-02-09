<?php

if (!function_exists('br_number')) {
    function br_number($value, int $decimals = 0): string
    {
        $num = is_numeric($value) ? (float)$value : 0.0;
        return number_format($num, $decimals, ',', '.');
    }
}

if (!function_exists('br_money')) {
    function br_money($value): string
    {
        return 'R$ ' . br_number($value, 2);
    }
}

if (!function_exists('br_date')) {
    function br_date(?string $date): string
    {
        if (!$date) return '';
        $ts = strtotime($date);
        return $ts ? date('d/m/Y', $ts) : $date;
    }
}

if (!function_exists('br_datetime')) {
    function br_datetime(?string $dateTime): string
    {
        if (!$dateTime) return '';
        $ts = strtotime($dateTime);
        return $ts ? date('d/m/Y H:i', $ts) : $dateTime;
    }
}
