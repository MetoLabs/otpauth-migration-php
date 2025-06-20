<?php

namespace MetoLabs\OtpAuthMigration\Tests;

use MetoLabs\OtpAuthMigration\OtpAuthMigration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OtpAuthMigrationTest extends TestCase
{
    /**
     * Instance of the decoder being tested.
     *
     * @var OtpAuthMigration
     */
    protected OtpAuthMigration $otp;

    /**
     * Set up a fresh decoder instance before each test.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->otp = OtpAuthMigration::make();
    }

    /**
     * Test decoding a valid otpauth-migration URL.
     *
     * @return void
     */
    public function testDecodeValidMigrationUrl(): void
    {
        $url = 'otpauth-migration://offline?data=CjQKFKuOm4V945mpzWoDXUoJMyfferkVEg5qZG9lQGdtYWlsLmNvbRoGQW1hem9uIAEoATACEAIYASAA';
        $results = $this->otp->decode($url);

        $this->assertIsArray($results);
        $this->assertNotEmpty($results);

        foreach ($results as $account) {
            $this->assertArrayHasKey('issuer', $account);
            $this->assertArrayHasKey('account', $account);
            $this->assertArrayHasKey('secret', $account);
        }
    }

    /**
     * Test that decoding an invalid migration URL throws an exception.
     *
     * @return void
     */
    public function testDecodeInvalidMigrationUrl(): void
    {
        $this->expectException(ProcessFailedException::class);
        $this->otp->decode('invalid-url');
    }
}
