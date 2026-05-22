<?php
namespace App\Exceptions;

class ValidationException extends \RuntimeException
{
    private array $errors;

    public function __construct(array $errors, string $message = 'Error de validación')
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): string
    {
        $first = reset($this->errors);
        return is_array($first) ? $first[0] : (string) $first;
    }
}
