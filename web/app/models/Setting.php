<?php
/**
 * Setting model.
 */
class Setting extends Model
{
    protected string $table = 'settings';

    /**
     * Get a setting value.
     */
    public function get(string $key, string $default = ''): string
    {
        $row = $this->firstWhere('setting_key', $key);
        return $row['setting_value'] ?? $default;
    }

    /**
     * Set a setting value (upsert).
     */
    public function set(string $key, string $value): void
    {
        $existing = $this->firstWhere('setting_key', $key);
        if ($existing) {
            $this->db->update('settings', ['setting_value' => $value], 'setting_key = :k', [':k' => $key]);
        } else {
            $this->create(['setting_key' => $key, 'setting_value' => $value]);
        }
    }
}
