<?php

namespace App\Services;

/**
 * Dependency-free TOTP (RFC 6238) — no Composer package, so it deploys cleanly to
 * shared hosting. Compatible with Google Authenticator, Authy, 1Password, etc.
 * (SHA1, 6 digits, 30-second period — the authenticator-app default).
 */
class TwoFactorAuthenticator
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // RFC 4648 base32

    private const DIGITS = 6;

    private const PERIOD = 30;

    /**
     * Generate a random base32 secret (default 160 bits of entropy).
     */
    public function generateSecretKey(int $length = 32): string
    {
        $bytes = random_bytes($length);
        $secret = '';

        for ($i = 0; $i < $length; $i++) {
            $secret .= self::ALPHABET[ord($bytes[$i]) & 31];
        }

        return $secret;
    }

    /**
     * Verify a submitted 6-digit code against the secret, allowing ±$window
     * time-steps for clock drift. Constant-time comparison.
     */
    public function verify(string $secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\s+/', '', $code);

        if (! preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $current = (int) floor(time() / self::PERIOD);

        for ($i = -$window; $i <= $window; $i++) {
            if (hash_equals($this->codeForCounter($secret, $current + $i), $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The otpauth:// URI encoded into the enrollment QR code.
     */
    public function otpauthUri(string $issuer, string $email, string $secret): string
    {
        $label = rawurlencode($issuer).':'.rawurlencode($email);

        // RFC 3986 encoding (spaces as %20, not +) — authenticator apps expect
        // percent-encoding in the otpauth URI.
        $query = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => self::DIGITS,
            'period' => self::PERIOD,
        ], '', '&', PHP_QUERY_RFC3986);

        return "otpauth://totp/{$label}?{$query}";
    }

    /**
     * The current code for a secret — used by tests and never exposed to users.
     */
    public function currentCode(string $secret): string
    {
        return $this->codeForCounter($secret, (int) floor(time() / self::PERIOD));
    }

    private function codeForCounter(string $secret, int $counter): string
    {
        $key = $this->base32Decode($secret);

        // 8-byte big-endian counter.
        $binary = pack('N*', 0).pack('N*', $counter);

        $hash = hash_hmac('sha1', $binary, $key, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;

        $value = ((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF);

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    private function base32Decode(string $base32): string
    {
        $base32 = rtrim(strtoupper($base32), '=');
        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        foreach (str_split($base32) as $char) {
            $index = strpos(self::ALPHABET, $char);

            if ($index === false) {
                continue; // skip anything outside the alphabet
            }

            $buffer = ($buffer << 5) | $index;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
