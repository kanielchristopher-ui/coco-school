<?php
/**
 * ============================================================
 *  OfficialId
 *  Monotonic, human-readable official ID generator
 *  (e.g. ELV-0001, ENS-0001, PAR-0001).
 *
 *  Numbers are allocated from the official_id_counters table so
 *  they never reuse deleted values and always continue from the
 *  latest generated number.
 * ============================================================
 */

declare(strict_types=1);

class OfficialId
{
    /**
     * Allocate and return the next official ID for a prefix.
     * Atomic under concurrency (single INSERT ... ON DUPLICATE
     * KEY UPDATE statement, no table locks / transactions).
     */
    public static function next(string $prefix): string
    {
        $prefix = strtoupper(trim($prefix));

        $db = Database::getInstance();
        $db->run(
            'INSERT INTO official_id_counters (prefix, last_number)
             VALUES (?, LAST_INSERT_ID(1))
             ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(last_number + 1)',
            [$prefix]
        );
        $number = (int) $db->fetchColumn('SELECT LAST_INSERT_ID()');

        return $prefix . '-' . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }
}
