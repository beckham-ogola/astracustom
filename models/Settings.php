<?php
/** AstraCampus - Settings Model (thin wrapper around helper functions) */

class Settings
{
    public static function get(string $key, $default = null)
    {
        return get_setting($key, $default);
    }

    public static function set(string $key, string $value, ?int $userId = null): void
    {
        set_setting($key, $value, $userId);
    }

    public static function all(): array
    {
        return db()->query('SELECT * FROM settings ORDER BY setting_group, setting_key')->fetchAll();
    }
}
