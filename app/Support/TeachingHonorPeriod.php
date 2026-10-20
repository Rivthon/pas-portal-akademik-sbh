<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class TeachingHonorPeriod
{
    public const CUTOFF_DAY = 20;

    public static function currentKey(?CarbonImmutable $today = null): string
    {
        $today ??= CarbonImmutable::now();
        $periodEnd = $today->day <= self::CUTOFF_DAY
            ? $today
            : $today->addMonthNoOverflow();

        return $periodEnd->format('Y-m');
    }

    public static function fromKey(?string $key): array
    {
        $key = preg_match('/^\d{4}-\d{2}$/', (string) $key)
            ? $key
            : self::currentKey();

        $end = CarbonImmutable::createFromFormat('!Y-m-d', $key.'-'.self::CUTOFF_DAY);
        $start = $end->subMonthNoOverflow()->day(self::CUTOFF_DAY)->addDay();

        return [
            'key' => $key,
            'start' => $start,
            'end' => $end,
            'label' => $start->translatedFormat('d F Y').' - '.$end->translatedFormat('d F Y'),
        ];
    }

    public static function options(int $months = 18): Collection
    {
        $current = CarbonImmutable::createFromFormat('!Y-m', self::currentKey());

        return collect(range(0, $months - 1))->map(function (int $offset) use ($current) {
            $month = $current->subMonthsNoOverflow($offset);
            $period = self::fromKey($month->format('Y-m'));

            return [
                'key' => $period['key'],
                'label' => $period['label'],
            ];
        });
    }
}
