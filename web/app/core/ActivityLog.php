<?php
/**
 * ============================================================
 *  ActivityLog
 *  Writes audit/activity entries to the activity_logs table.
 * ============================================================
 */

declare(strict_types=1);

class ActivityLog
{
    /**
     * Create a new activity log entry.
     */
    public static function log(string $action, string $module, string $description = ''): void
    {
        try {
            Database::getInstance()->insert('activity_logs', [
                'user_id'    => Auth::check() ? Auth::id() : null,
                'action'     => $action,
                'module'     => $module,
                'description'=> $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (Throwable $e) {
            // Never let logging break the request.
        }
    }
}
