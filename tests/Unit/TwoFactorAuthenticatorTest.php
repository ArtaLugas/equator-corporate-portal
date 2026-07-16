<?php

namespace Tests\Unit;

use App\Services\TwoFactorAuthenticator;
use PHPUnit\Framework\TestCase;

class TwoFactorAuthenticatorTest extends TestCase
{
    public function test_secret_key_is_valid_base32(): void
    {
        $secret = (new TwoFactorAuthenticator)->generateSecretKey();

        $this->assertSame(32, strlen($secret));
        $this->assertMatchesRegularExpression('/^[A-Z2-7]+$/', $secret);
    }

    public function test_verify_accepts_the_current_code(): void
    {
        $svc = new TwoFactorAuthenticator;
        $secret = $svc->generateSecretKey();

        $this->assertTrue($svc->verify($secret, $svc->currentCode($secret)));
    }

    public function test_verify_rejects_a_code_from_a_different_secret(): void
    {
        $svc = new TwoFactorAuthenticator;
        $secret = $svc->generateSecretKey();
        $other = $svc->generateSecretKey();

        $this->assertFalse($svc->verify($secret, $svc->currentCode($other)));
        $this->assertFalse($svc->verify($secret, 'abc'));
    }

    public function test_otpauth_uri_carries_secret_and_issuer(): void
    {
        $uri = (new TwoFactorAuthenticator)->otpauthUri('Equator Group', 'a@b.com', 'JBSWY3DPEHPK3PXP');

        $this->assertStringStartsWith('otpauth://totp/', $uri);
        $this->assertStringContainsString('secret=JBSWY3DPEHPK3PXP', $uri);
        $this->assertStringContainsString('issuer=Equator%20Group', $uri);
    }
}
