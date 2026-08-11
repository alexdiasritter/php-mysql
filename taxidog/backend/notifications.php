<?php
/**
 * Avisos que o app busca periodicamente em frontend/check_notifications.php.
 */
class Notifications
{
    public static function pendingFor(int $userId): array
    {
        return array_merge(
            self::upcomingRides($userId),
            self::morningSummary($userId)
        );
    }

    /** Corridas que começam na próxima hora e ainda não foram avisadas */
    private static function upcomingRides(int $userId): array
    {
        $now     = date('Y-m-d H:i:s');
        $oneHour = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $notifications = [];
        foreach (Rides::pendingNotNotifiedBetween($userId, $now, $oneHour) as $ride) {
            $notifications[] = [
                'id'    => $ride['id'],
                'title' => '🚕 Corrida em 1 hora!',
                'body'  => sprintf(
                    '%s para %s às %s',
                    $ride['origin'],
                    $ride['destination'],
                    date('H:i', strtotime($ride['ride_date']))
                ),
            ];

            // Marca como notificado para não repetir a cada consulta
            Rides::markNotified((int) $ride['id']);
        }

        return $notifications;
    }

    /** Resumo do dia, entre 06:00 e 09:00, uma vez por sessão */
    private static function morningSummary(int $userId): array
    {
        $hour = (int) date('H');
        if ($hour < 6 || $hour > 9) {
            return [];
        }

        $today = date('Y-m-d');
        $sessionKey = 'morning_notified_' . $today;

        if (Session::has($sessionKey)) {
            return [];
        }

        $total = Rides::countPendingOnDate($userId, $today);
        if ($total === 0) {
            return [];
        }

        Session::set($sessionKey, true);

        return [[
            'id'    => 'morning_' . $today,
            'title' => '☀️ Bom dia!',
            'body'  => "Você tem {$total} corrida(s) pendente(s) hoje.",
        ]];
    }
}
