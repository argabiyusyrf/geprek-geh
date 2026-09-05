<?php
/**
 * TOTP (RFC 6238) — Time-based One-Time Password.
 * Kompatibel dengan Google Authenticator, Authy, 1Password, dll.
 */
class Totp {
    private const PERIOD  = 30;
    private const DIGITS  = 6;
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /** Buat secret acak dalam format Base32 (RFC 4648). */
    public static function generateSecret($bytes = 20) {
        $secret = '';
        foreach (str_split(random_bytes($bytes)) as $byte) {
            $secret .= self::ALPHABET[ord($byte) & 31];
        }
        return $secret;
    }

    /** Decode string Base32 (capital, bisa ada spasi) menjadi byte. */
    public static function base32Decode($secret) {
        $secret = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $secret ?? ''));
        $bits   = '';
        $len    = strlen($secret);
        for ($i = 0; $i < $len; $i++) {
            $v = strpos(self::ALPHABET, $secret[$i]);
            if ($v === false) continue;
            $bits .= str_pad(decbin($v), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        for ($i = 0; $i + 8 <= strlen($bits); $i += 8) {
            $bytes .= chr(bindec(substr($bits, $i, 8)));
        }
        return $bytes;
    }

    /** Hitung 6 digit TOTP untuk waktu (epoch detik; default: sekarang). */
    public static function code($secret, $time = null) {
        $counter = intdiv($time ?? time(), self::PERIOD);
        $bin     = "\0\0\0\0" . pack('N', $counter);
        $hash    = hash_hmac('sha1', $bin, self::base32Decode($secret), true);
        $offset  = ord(substr($hash, -1)) & 0x0f;
        $value   = unpack('N', substr($hash, $offset, 4))[1];
        $otp     = ($value & 0x7fffffff) % (10 ** self::DIGITS);
        return str_pad((string) $otp, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /** Verifikasi kode (mentolerir drift ±$window periode). */
    public static function verify($secret, $code, $window = 1) {
        $code = trim($code ?? '');
        if (!preg_match('/^\d{6}$/', $code)) return false;
        $now = time();
        for ($k = -$window; $k <= $window; $k++) {
            $expected = self::code($secret, $now + ($k * self::PERIOD));
            if (hash_equals($expected, $code)) return true;
        }
        return false;
    }

    /** URI otpauth:// untuk QR / import manual ke aplikasi authenticator. */
    public static function otpauthUri($label, $secret, $issuer = 'Geprek Geh') {
        $query = http_build_query([
            'secret'    => $secret,
            'issuer'    => $issuer,
            'algorithm' => 'SHA1',
            'digits'    => self::DIGITS,
            'period'    => self::PERIOD,
        ]);
        return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($label) . '?' . $query;
    }

    /** Buat 8 recovery code sekali pakai (masing-masing 8 karakter). */
    public static function recoveryCodes($count = 8) {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(6)), 0, 8));
        }
        return $codes;
    }

    /**
     * Cocokkan recovery code terhadap kumpulan hash JSON tersimpan.
     * Mengembalikan array hash baru (tanpa kode terpakai), atau null bila tak cocok.
     */
    public static function matchRecovery($storedJson, $code) {
        $hashes = json_decode($storedJson ?? '[]', true);
        if (!is_array($hashes) || empty($hashes)) return null;
        $code = strtoupper(trim($code ?? ''));
        if ($code === '') return null;
        foreach ($hashes as $i => $hash) {
            if (is_string($hash) && password_verify($code, $hash)) {
                unset($hashes[$i]);
                return array_values($hashes);
            }
        }
        return null;
    }
}