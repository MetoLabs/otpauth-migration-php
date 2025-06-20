<?php

namespace MetoLabs\OtpAuthMigration;

use InvalidArgumentException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OtpAuthMigration
{
    /**
     * Path to binary otpauth
     *
     * @var string
     */
    protected string $binaryPath;

    /**
     * OtpAuth constructor.
     *
     * @param string|null $binaryPath Path to the otpauth binary (default: 'otpauth' from $PATH)
     */
    public function __construct(?string $binaryPath = null)
    {
        $this->binaryPath = $binaryPath ?? 'otpauth';

        if ($this->binaryPath === 'otpauth') {
            $path = exec('which otpauth');

            if (empty($path) || !is_executable($path)) {
                throw new InvalidArgumentException('otpauth binary not found in $PATH or not executable.');
            }

            $this->binaryPath = $path;
        } elseif (! is_executable($this->binaryPath)) {
            throw new InvalidArgumentException("The provided binary path '{$this->binaryPath}' is not executable.");
        }
    }

    /**
     *  Make.
     *
     * @param string|null $binaryPath
     * @return self
     */
    public static function make(?string $binaryPath = null): self
    {
        return new self($binaryPath);
    }

    /**
     * Decode an otpauth-migration URL into a list of secrets.
     *
     * @param string $migrationUrl
     * @param array<string, string>|null $env Optional environment variables to pass to the process.
     * @param string|null $cwd Optional working directory. Defaults to PHP temp dir.
     * @return array<int, array{issuer: string, account: string, secret: string}>
     *
     * @throws ProcessFailedException
     */
    public function decode(string $migrationUrl, ?array $env = null, ?string $cwd = null): array
    {
        $cwd = $cwd ?? sys_get_temp_dir();
        $binPath = $cwd . DIRECTORY_SEPARATOR . 'migration.bin';

        $process = new Process([$this->binaryPath, '-link', $migrationUrl], $cwd, $env);
        $process->run();

        try {
            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $lines = explode("\n", trim($process->getOutput()));
            $results = [];

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }

                $parsed = parse_url($line);

                if ($parsed === false || !isset($parsed['scheme'], $parsed['host'], $parsed['path'], $parsed['query'])) {
                    continue; // Invalid URI
                }

                $account = ltrim($parsed['path'], '/');

                parse_str($parsed['query'], $queryParams);

                $issuer = $queryParams['issuer'] ?? '';
                $secret = $queryParams['secret'] ?? '';

                $results[] = [
                    'issuer' => $issuer,
                    'account' => $account,
                    'secret' => $secret,
                    'algorithm' => $queryParams['algorithm'] ?? null,
                    'digits' => isset($queryParams['digits']) ? (int)$queryParams['digits'] : null,
                    'period' => isset($queryParams['period']) ? (int)$queryParams['period'] : null,
                ];
            }

            return $results;
        } finally {
            if (file_exists($binPath)) {
                @unlink($binPath);
            }
        }
    }
}