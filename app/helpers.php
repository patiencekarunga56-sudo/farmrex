<?php

if (! function_exists('ugx')) {
    /** Format an amount as Uganda Shillings. */
    function ugx($amount, bool $symbol = true): string
    {
        $v = number_format((float) $amount, 0);

        return $symbol ? 'UGX '.$v : $v;
    }
}

if (! function_exists('palette')) {
    /** Chart colours taken from the reference dashboard. */
    function palette(int $i): string
    {
        $c = ['#74d24a', '#f1cb32', '#3cc3df', '#fa3df3', '#d7085f', '#3661ce', '#8c63da', '#7bf8b3'];

        return $c[$i % count($c)];
    }
}

if (! function_exists('badge_class')) {
    /** Map a status-like word to a badge colour class. */
    function badge_class(?string $value): string
    {
        $v = strtolower((string) $value);

        return match (true) {
            in_array($v, ['active', 'done', 'completed', 'harvested', 'low', 'grade a', 'planted', 'administrator']) => 'badge-green',
            in_array($v, ['pending', 'medium', 'grade b', 'germination', 'ready for harvest', 'harvesting']) => 'badge-amber',
            in_array($v, ['high', 'inactive', 'overdue', 'pest', 'disease', 'grade c']) => 'badge-red',
            in_array($v, ['vegetative', 'flowering', 'fruiting', 'farmer', 'growth stage update']) => 'badge-blue',
            default => 'badge-gray',
        };
    }
}
