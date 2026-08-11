<?php
/**
 * Monta todos os números do dashboard de ganhos.
 * A view só recebe arrays prontos — nenhuma consulta acontece lá.
 */
class Earnings
{
    private const DIAS_TENDENCIA = 30;
    private const MESES_COMPARATIVO = 6;
    private const TOP_BAIRROS = 5;

    public static function dashboard(int $userId): array
    {
        $monthStats = Rides::monthStats($userId, date('m'), date('Y'));
        $weekStats  = Rides::weekStats($userId, date('W'), date('Y'));

        [$daysLabels, $daysValues] = self::dailySeries($userId);
        [$monthsLabels, $monthsValues, $monthsCounts] = self::monthlySeries($userId);
        $monthsAvg = self::averagePerRide($monthsValues, $monthsCounts);
        $rankings  = self::rankings($userId);

        return [
            'week_stats'  => $weekStats,
            'month_stats' => $monthStats,
            'charts'      => [
                'days' => [
                    'labels' => $daysLabels,
                    'values' => $daysValues,
                ],
                'overview' => [
                    'week_total' => (float) ($weekStats['total_earnings'] ?? 0),
                    'month_total' => (float) ($monthStats['total_earnings'] ?? 0),
                    'week_rides' => (int) ($weekStats['total_rides'] ?? 0),
                    'month_rides' => (int) ($monthStats['total_rides'] ?? 0),
                ],
                'months' => [
                    'labels' => $monthsLabels,
                    'values' => $monthsValues,
                    'counts' => $monthsCounts,
                    'avg'    => $monthsAvg,
                ],
                'origins'  => $rankings['origins'],
                'destinations' => $rankings['destinations'],
                'clinics'  => $rankings['clinics'],
            ],
        ];
    }

    /** Série diária dos últimos 30 dias, preenchendo com 0 os dias sem corrida */
    private static function dailySeries(int $userId): array
    {
        $raw = Rides::dailyEarnings($userId, self::DIAS_TENDENCIA);
        $byDay = array_column($raw, 'daily_earnings', 'ride_day');

        $labels = [];
        $values = [];
        for ($i = self::DIAS_TENDENCIA - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $labels[] = date('d/m', strtotime($date));
            $values[] = (float) ($byDay[$date] ?? 0);
        }

        return [$labels, $values];
    }

    /** Série mensal dos últimos 6 meses, preenchendo com 0 os meses vazios */
    private static function monthlySeries(int $userId): array
    {
        $raw = Rides::monthlyTotals($userId, self::MESES_COMPARATIVO);
        $totals = array_column($raw, 'total', 'ym');
        $counts = array_column($raw, 'qty', 'ym');
        $meses  = meses_abreviados();

        $labels = [];
        $values = [];
        $qty    = [];
        for ($i = self::MESES_COMPARATIVO - 1; $i >= 0; $i--) {
            $dt = new DateTime('first day of this month');
            $dt->modify("-$i months");
            $ym = $dt->format('Y-m');

            $labels[] = $meses[(int) $dt->format('n') - 1] . '/' . $dt->format('y');
            $values[] = (float) ($totals[$ym] ?? 0);
            $qty[]    = (int) ($counts[$ym] ?? 0);
        }

        return [$labels, $values, $qty];
    }

    private static function averagePerRide(array $totals, array $counts): array
    {
        $avg = [];
        foreach ($totals as $i => $total) {
            $qty = $counts[$i] ?? 0;
            $avg[] = $qty > 0 ? round($total / $qty, 2) : 0;
        }
        return $avg;
    }

    /**
     * Rankings de bairros de origem/destino e de clínicas.
     * Um campo que bate com o nome de uma clínica não conta como bairro.
     */
    private static function rankings(int $userId): array
    {
        $clinicas = config('clinicas', []);
        $clinicasNorm = [];
        foreach ($clinicas as $nome) {
            $clinicasNorm[$nome] = self::normalize($nome);
        }

        $clinicCounts = array_fill_keys($clinicas, 0);
        $originCounts = [];
        $destCounts   = [];

        foreach (Rides::completedRoutes($userId) as $ride) {
            $originNorm = self::normalize($ride['origin'] ?? '');
            $destNorm   = self::normalize($ride['destination'] ?? '');

            $originClinic = self::matchClinic($originNorm, $clinicasNorm);
            $destClinic   = self::matchClinic($destNorm, $clinicasNorm);

            // Cada clínica conta no máximo 1x por corrida
            foreach (array_unique(array_filter([$originClinic, $destClinic])) as $clinic) {
                $clinicCounts[$clinic]++;
            }

            if (!$originClinic && $originNorm !== '') {
                self::bump($originCounts, $originNorm);
            }
            if (!$destClinic && $destNorm !== '') {
                self::bump($destCounts, $destNorm);
            }
        }

        arsort($clinicCounts);
        $clinicCounts = array_filter($clinicCounts, fn($v) => $v > 0);

        return [
            'origins'      => self::topN($originCounts),
            'destinations' => self::topN($destCounts),
            'clinics'      => [
                'labels' => array_keys($clinicCounts),
                'values' => array_values($clinicCounts),
            ],
        ];
    }

    private static function bump(array &$counts, string $key): void
    {
        if (!isset($counts[$key])) {
            $counts[$key] = [
                'label' => mb_convert_case($key, MB_CASE_TITLE, 'UTF-8'),
                'count' => 0,
            ];
        }
        $counts[$key]['count']++;
    }

    private static function topN(array $counts): array
    {
        uasort($counts, fn($a, $b) => $b['count'] <=> $a['count']);
        $counts = array_slice($counts, 0, self::TOP_BAIRROS, true);

        return [
            'labels' => array_values(array_column($counts, 'label')),
            'values' => array_values(array_column($counts, 'count')),
        ];
    }

    private static function matchClinic(string $text, array $clinicasNorm): ?string
    {
        foreach ($clinicasNorm as $nome => $norm) {
            if ($norm !== '' && str_contains($text, $norm)) {
                return $nome;
            }
        }
        return null;
    }

    /** Minúsculas, sem acento e sem pontuação — para comparar textos digitados à mão */
    private static function normalize(string $str): string
    {
        $map = [
            'á'=>'a','à'=>'a','ã'=>'a','â'=>'a','ä'=>'a',
            'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'í'=>'i','ì'=>'i','î'=>'i','ï'=>'i',
            'ó'=>'o','ò'=>'o','õ'=>'o','ô'=>'o','ö'=>'o',
            'ú'=>'u','ù'=>'u','û'=>'u','ü'=>'u',
            'ç'=>'c','ñ'=>'n',
            'Á'=>'a','À'=>'a','Ã'=>'a','Â'=>'a','Ä'=>'a',
            'É'=>'e','È'=>'e','Ê'=>'e','Ë'=>'e',
            'Í'=>'i','Ì'=>'i','Î'=>'i','Ï'=>'i',
            'Ó'=>'o','Ò'=>'o','Õ'=>'o','Ô'=>'o','Ö'=>'o',
            'Ú'=>'u','Ù'=>'u','Û'=>'u','Ü'=>'u',
            'Ç'=>'c','Ñ'=>'n',
        ];

        $str = strtr($str, $map);
        $str = mb_strtolower($str, 'UTF-8');
        $str = preg_replace('/[^a-z0-9 ]/', ' ', $str);

        return trim(preg_replace('/\s+/', ' ', $str));
    }
}
