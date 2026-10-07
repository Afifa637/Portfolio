<?php

/**
 * Optional database access.
 *
 * The public site treats MySQL as an enhancement, not a requirement. If the
 * server is down, misconfigured, or simply absent, connection() returns null
 * and every caller falls back to config/profile.php. This is what makes the
 * portfolio deployable to hosting where no database has been provisioned yet.
 */

declare(strict_types=1);

final class Database
{
    private static ?mysqli $connection = null;
    private static bool $attempted = false;
    private static ?string $error = null;

    /** @var array<string, bool>|null Cached table-existence map. */
    private static ?array $tables = null;

    /**
     * Seconds to suppress reconnection attempts after a failure.
     *
     * Without this, every request pays the full TCP timeout while the database
     * is down — roughly two seconds per page on Windows, which dwarfs the rest
     * of the render. One request absorbs the cost, the rest skip straight to
     * the file content.
     */
    private const BREAKER_SECONDS = 60;

    public static function connection(): ?mysqli
    {
        if (self::$attempted) {
            return self::$connection;
        }

        self::$attempted = true;

        // An explicit opt-out for deployments that intentionally run without a
        // database; the site is fully functional from config/profile.php.
        if (env('DB_ENABLED', true) === false) {
            return null;
        }

        if (!extension_loaded('mysqli')) {
            self::$error = 'The mysqli extension is not loaded.';
            return null;
        }

        if (self::breakerOpen()) {
            self::$error = 'Connection suppressed after a recent failure.';
            return null;
        }

        // Suppress mysqli's exception mode so a dead socket does not abort the
        // page; we want a null handle and a rendered site instead. mysqli_report()
        // returns a bool rather than the prior flags, so restore PHP's default
        // (ERROR|STRICT, the 8.1+ baseline) in the finally block.
        mysqli_report(MYSQLI_REPORT_OFF);

        try {
            // mysqli_init() + real_connect() is the only route that allows a
            // connect timeout to be set; the constructor ignores it.
            $conn = mysqli_init();

            if (!$conn) {
                self::$error = 'mysqli_init() failed.';
                return null;
            }

            $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, (int) env('DB_TIMEOUT', 3));

            $ok = @$conn->real_connect(
                (string) env('DB_HOST', '127.0.0.1'),
                (string) env('DB_USERNAME', 'root'),
                (string) env('DB_PASSWORD', ''),
                (string) env('DB_DATABASE', 'portfolio_db'),
                (int) env('DB_PORT', 3306)
            );

            if (!$ok || $conn->connect_errno) {
                self::$error = $conn->connect_error ?: 'Connection refused.';
                self::tripBreaker();
                error_log('[portfolio] DB unavailable, using file content: ' . self::$error);

                return null;
            }

            $conn->set_charset('utf8mb4');
            self::$connection = $conn;
        } catch (Throwable $e) {
            self::$error = $e->getMessage();
            self::tripBreaker();
            error_log('[portfolio] DB unavailable, using file content: ' . $e->getMessage());
        } finally {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        }

        return self::$connection;
    }

    private static function breakerFile(): string
    {
        return APP_ROOT . '/storage/cache/db-down.lock';
    }

    private static function breakerOpen(): bool
    {
        $file = self::breakerFile();

        if (!is_file($file)) {
            return false;
        }

        if ((time() - (int) @filemtime($file)) < self::BREAKER_SECONDS) {
            return true;
        }

        @unlink($file);

        return false;
    }

    private static function tripBreaker(): void
    {
        $dir = dirname(self::breakerFile());

        if (is_dir($dir) || @mkdir($dir, 0775, true) || is_dir($dir)) {
            @touch(self::breakerFile());
        }
    }

    public static function available(): bool
    {
        return self::connection() instanceof mysqli;
    }

    public static function lastError(): ?string
    {
        return self::$error;
    }

    /** True when the connection is live and the named table exists. */
    public static function hasTable(string $table): bool
    {
        $conn = self::connection();

        if (!$conn) {
            return false;
        }

        if (self::$tables === null) {
            self::$tables = [];
            $result = @$conn->query('SHOW TABLES');

            if ($result) {
                while ($row = $result->fetch_array(MYSQLI_NUM)) {
                    self::$tables[strtolower((string) $row[0])] = true;
                }
                $result->free();
            }
        }

        return isset(self::$tables[strtolower($table)]);
    }

    /**
     * Fetch all rows for a query, returning [] on any failure.
     *
     * @param  list<mixed> $params
     * @return list<array<string, mixed>>
     */
    public static function all(string $sql, array $params = []): array
    {
        $conn = self::connection();

        if (!$conn) {
            return [];
        }

        try {
            if ($params === []) {
                $result = @$conn->query($sql);

                if (!$result) {
                    return [];
                }

                $rows = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();

                return $rows ?: [];
            }

            $stmt = @$conn->prepare($sql);

            if (!$stmt) {
                return [];
            }

            $stmt->bind_param(self::types($params), ...$params);
            $stmt->execute();

            $result = $stmt->get_result();
            $rows   = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

            $stmt->close();

            return $rows ?: [];
        } catch (Throwable $e) {
            error_log('[portfolio] query failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * @param  list<mixed> $params
     * @return array<string, mixed>|null
     */
    public static function first(string $sql, array $params = []): ?array
    {
        return self::all($sql, $params)[0] ?? null;
    }

    /** @param list<mixed> $params */
    public static function execute(string $sql, array $params = []): bool
    {
        $conn = self::connection();

        if (!$conn) {
            return false;
        }

        try {
            $stmt = @$conn->prepare($sql);

            if (!$stmt) {
                return false;
            }

            if ($params !== []) {
                $stmt->bind_param(self::types($params), ...$params);
            }

            $ok = $stmt->execute();
            $stmt->close();

            return $ok;
        } catch (Throwable $e) {
            error_log('[portfolio] statement failed: ' . $e->getMessage());
            return false;
        }
    }

    /** @param list<mixed> $params */
    private static function types(array $params): string
    {
        $types = '';

        foreach ($params as $param) {
            $types .= match (true) {
                is_int($param)   => 'i',
                is_float($param) => 'd',
                default          => 's',
            };
        }

        return $types;
    }
}
