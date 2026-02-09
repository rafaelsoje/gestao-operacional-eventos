<?php

if (!function_exists('parse_money_br')) {
    function parse_money_br(?string $value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }

        $value = str_replace(['R$', ' '], '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float)$value;
    }
}

if (!function_exists('money_to_decimal')) {
    function money_to_decimal(?string $value): string
    {
        $num = parse_money_br($value);
        return number_format($num, 2, '.', '');
    }
}
