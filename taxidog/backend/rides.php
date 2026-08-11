<?php
/**
 * Acesso à tabela `rides`. Toda consulta é filtrada por user_id.
 */
class Rides
{
    public static function create(int $userId, array $data): void
    {
        $stmt = db()->prepare(
            'INSERT INTO rides (user_id, client, origin, destination, price, period, animal_quantity, ride_date, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "pending")'
        );
        $stmt->execute([
            $userId,
            $data['client'],
            $data['origin'],
            $data['destination'],
            $data['price'],
            $data['period'],
            $data['animal_quantity'],
            $data['ride_date'],
        ]);
    }

    public static function find(int $id, int $userId): ?array
    {
        $stmt = db()->prepare('SELECT * FROM rides WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        $ride = $stmt->fetch();
        return $ride ?: null;
    }

    public static function update(int $id, int $userId, array $data): bool
    {
        $stmt = db()->prepare(
            'UPDATE rides SET ride_date = ?, client = ?, origin = ?, destination = ?,
                    animal_quantity = ?, period = ?, price = ?
             WHERE id = ? AND user_id = ?'
        );
        return $stmt->execute([
            $data['ride_date'],
            $data['client'],
            $data['origin'],
            $data['destination'],
            $data['animal_quantity'],
            $data['period'],
            $data['price'],
            $id,
            $userId,
        ]);
    }

    public static function updateStatus(int $id, int $userId, string $status): void
    {
        $stmt = db()->prepare('UPDATE rides SET status = ? WHERE id = ? AND user_id = ?');
        $stmt->execute([$status, $id, $userId]);
    }

    /** Corridas pendentes, da mais próxima para a mais distante */
    public static function pending(int $userId): array
    {
        $stmt = db()->prepare(
            "SELECT * FROM rides WHERE user_id = ? AND status = 'pending' ORDER BY ride_date ASC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Corridas concluídas dentro de um intervalo de datas (YYYY-MM-DD) */
    public static function completedBetween(int $userId, string $startDate, string $endDate): array
    {
        $stmt = db()->prepare(
            "SELECT client, price, ride_date, status, origin, destination
             FROM rides
             WHERE user_id = ? AND DATE(ride_date) BETWEEN ? AND ? AND status = 'completed'
             ORDER BY ride_date ASC, id ASC"
        );
        $stmt->execute([$userId, $startDate, $endDate]);
        return $stmt->fetchAll();
    }

    /** Total e quantidade de concluídas em um mês */
    public static function monthStats(int $userId, string $month, string $year): array
    {
        $stmt = db()->prepare(
            "SELECT SUM(price) AS total_earnings, COUNT(id) AS total_rides
             FROM rides
             WHERE user_id = ? AND status = 'completed' AND MONTH(ride_date) = ? AND YEAR(ride_date) = ?"
        );
        $stmt->execute([$userId, $month, $year]);
        return $stmt->fetch() ?: ['total_earnings' => 0, 'total_rides' => 0];
    }

    /** Total e quantidade de concluídas em uma semana ISO */
    public static function weekStats(int $userId, string $week, string $year): array
    {
        $stmt = db()->prepare(
            "SELECT SUM(price) AS total_earnings, COUNT(id) AS total_rides
             FROM rides
             WHERE user_id = ? AND status = 'completed' AND WEEK(ride_date, 1) = ? AND YEAR(ride_date) = ?"
        );
        $stmt->execute([$userId, $week, $year]);
        return $stmt->fetch() ?: ['total_earnings' => 0, 'total_rides' => 0];
    }

    /** Soma por dia nos últimos N dias */
    public static function dailyEarnings(int $userId, int $days): array
    {
        // $days é sempre interno (nunca vem da requisição), então pode ir no SQL
        // como inteiro — INTERVAL não aceita placeholder de forma confiável.
        $interval = max(0, $days - 1);
        $stmt = db()->prepare(
            "SELECT DATE(ride_date) AS ride_day, SUM(price) AS daily_earnings
             FROM rides
             WHERE user_id = ? AND status = 'completed'
               AND ride_date >= DATE_SUB(CURDATE(), INTERVAL {$interval} DAY)
             GROUP BY ride_day ORDER BY ride_day ASC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Soma e quantidade por mês nos últimos N meses */
    public static function monthlyTotals(int $userId, int $months): array
    {
        $interval = max(0, $months - 1);
        $stmt = db()->prepare(
            "SELECT DATE_FORMAT(ride_date, '%Y-%m') AS ym, SUM(price) AS total, COUNT(id) AS qty
             FROM rides
             WHERE user_id = ? AND status = 'completed'
               AND ride_date >= DATE_SUB(CURDATE(), INTERVAL {$interval} MONTH)
             GROUP BY ym ORDER BY ym ASC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Origem/destino de todas as concluídas (para os rankings) */
    public static function completedRoutes(int $userId): array
    {
        $stmt = db()->prepare(
            "SELECT origin, destination FROM rides WHERE user_id = ? AND status = 'completed'"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /** Pendentes que começam dentro do intervalo e ainda não foram notificadas */
    public static function pendingNotNotifiedBetween(int $userId, string $from, string $to): array
    {
        $stmt = db()->prepare(
            "SELECT * FROM rides
             WHERE user_id = ? AND status = 'pending' AND notified = 0
               AND ride_date BETWEEN ? AND ?"
        );
        $stmt->execute([$userId, $from, $to]);
        return $stmt->fetchAll();
    }

    public static function markNotified(int $id): void
    {
        $stmt = db()->prepare('UPDATE rides SET notified = 1 WHERE id = ?');
        $stmt->execute([$id]);
    }

    public static function countPendingOnDate(int $userId, string $date): int
    {
        $stmt = db()->prepare(
            "SELECT COUNT(*) AS total FROM rides
             WHERE user_id = ? AND status = 'pending' AND DATE(ride_date) = ?"
        );
        $stmt->execute([$userId, $date]);
        $row = $stmt->fetch();
        return (int) ($row['total'] ?? 0);
    }

    /** Agrupa uma lista de corridas por dia (YYYY-MM-DD) */
    public static function groupByDay(array $rides): array
    {
        $byDay = [];
        foreach ($rides as $ride) {
            $day = date('Y-m-d', strtotime($ride['ride_date']));
            $byDay[$day][] = $ride;
        }
        ksort($byDay);
        return $byDay;
    }
}
