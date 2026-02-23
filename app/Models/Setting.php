<?php

namespace App\Models;

use Etus\Framework\Database\Model;
use Etus\Framework\Database\Connection;

class Setting extends Model
{
    protected string $table      = 'system_settings';
    protected string $primaryKey = 'id';
    protected bool   $timestamps  = true;
    protected bool   $auditFields = false;
    protected bool   $logging     = true;

    /** @var array<string, string> In-memory cache */
    private static array $cache = [];

    // ── Read ──────────────────────────────────────────────────────────────────

    /**
     * Get a setting value by key. Returns $default if not found.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        if (! isset(self::$cache[$key])) {
            $row = Connection::getInstance()->selectOne(
                'SELECT value, type FROM system_settings WHERE `key` = ? LIMIT 1',
                [$key],
            );

            if (! $row) return $default;

            self::$cache[$key] = self::castValue($row['value'], $row['type']);
        }

        return self::$cache[$key] ?? $default;
    }

    /**
     * Get all settings as flat key → value map.
     *
     * @return array<string, mixed>
     */
    public static function get_all(): array
    {
        $rows = Connection::getInstance()->select(
            'SELECT `key`, value, type FROM system_settings ORDER BY "group" ASC, `key` ASC'
        );

        $result = [];
        foreach ($rows as $row) {
            $result[$row['key']] = self::castValue($row['value'], $row['type']);
        }

        return $result;
    }

    /**
     * Get all settings grouped by their group column, with full row data.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function grouped(): array
    {
        $rows = Connection::getInstance()->select(
            'SELECT * FROM system_settings ORDER BY "group" ASC, label ASC'
        );

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row['group']][] = $row;
        }

        return $grouped;
    }

    // ── Write ─────────────────────────────────────────────────────────────────

    /**
     * Persist a setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        $strValue = is_bool($value) ? ($value ? '1' : '0') : (string) $value;

        Connection::getInstance()->execute(
            'UPDATE system_settings SET value = ?, updated_at = ? WHERE `key` = ?',
            [$strValue, date('Y-m-d H:i:s'), $key],
        );

        // Bust cache
        unset(self::$cache[$key]);
    }

    /**
     * Persist many settings at once.
     *
     * @param array<string, mixed> $data
     */
    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            self::set($key, $value);
        }
    }

    public static function flushCache(): void
    {
        self::$cache = [];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private static function castValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'boolean' => (bool) (int) $value,
            'number'  => is_numeric($value) ? (float) $value : $value,
            default   => $value,
        };
    }
}
