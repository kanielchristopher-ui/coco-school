<?php
/**
 * ============================================================
 *  Flash
 *  One-time flash messages stored in the session.
 * ============================================================
 */

declare(strict_types=1);

class Flash
{
    /**
     * Queue a flash message.
     *
     * @param string $type success | error | warning | info
     */
    public static function set(string $type, string $message): void
    {
        $messages = Session::get('flash_messages', []);
        $messages[] = ['type' => $type, 'message' => $message];
        Session::set('flash_messages', $messages);
    }

    /**
     * Retrieve and clear queued messages.
     */
    public static function pull(): array
    {
        $messages = Session::get('flash_messages', []);
        Session::remove('flash_messages');
        return $messages;
    }

    /**
     * Output all queued flash messages as Bootstrap alerts.
     */
    public static function display(): void
    {
        foreach (self::pull() as $item) {
            $type = $item['type'];
            $colorMap = [
                'success' => 'success',
                'error'   => 'danger',
                'warning' => 'warning',
                'info'    => 'info',
            ];
            $color = $colorMap[$type] ?? 'info';
            echo '<div class="alert alert-' . $color . ' alert-dismissible fade show shadow-sm" role="alert">'
                . e($item['message'])
                . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'
                . '</div>';
        }
    }
}
