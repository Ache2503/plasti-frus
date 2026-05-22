<?php
namespace App\Exceptions;

class AuthException extends \RuntimeException
{
    public const LOGIN_REQUIRED = 1;
    public const INVALID_CREDENTIALS = 2;
    public const INSUFFICIENT_ROLE = 3;
    public const ACCESS_DENIED = 4;

    private int $reason;

    public function __construct(string $message, int $reason = self::ACCESS_DENIED)
    {
        parent::__construct($message);
        $this->reason = $reason;
    }

    public function getReason(): int
    {
        return $this->reason;
    }
}
