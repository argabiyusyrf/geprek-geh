<?php
class RateLimiter {
    private static string $dir = __DIR__ . '/../logs/ratelimit';

    public static function attempt(string $key, int $maxAttempts, int $windowSeconds): bool {
        if (!is_dir(self::$dir)) mkdir(self::$dir, 0755, true);
        $file = self::$dir . '/' . md5($key) . '.json';
        $now = time();

        $data = ['attempts' => [], 'blocked_until' => 0];
        if (is_file($file)) {
            $raw = file_get_contents($file);
            if ($raw !== false) $data = json_decode($raw, true) ?? $data;
        }

        if (($data['blocked_until'] ?? 0) > $now) return false;

        $data['attempts'] = array_filter($data['attempts'] ?? [], fn($t) => $t > $now - $windowSeconds);
        $data['attempts'][] = $now;

        if (count($data['attempts']) > $maxAttempts) {
            $data['blocked_until'] = $now + $windowSeconds;
            file_put_contents($file, json_encode($data));
            return false;
        }

        file_put_contents($file, json_encode($data));
        return true;
    }

    public static function remaining(string $key, int $maxAttempts, int $windowSeconds): int {
        if (!is_dir(self::$dir)) return $maxAttempts;
        $file = self::$dir . '/' . md5($key) . '.json';
        if (!is_file($file)) return $maxAttempts;
        $data = json_decode(file_get_contents($file), true);
        if (!$data) return $maxAttempts;
        $now = time();
        $attempts = array_filter($data['attempts'] ?? [], fn($t) => $t > $now - $windowSeconds);
        return max(0, $maxAttempts - count($attempts));
    }
}
