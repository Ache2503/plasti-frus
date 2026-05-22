<?php
namespace App\Exceptions;

class NotFoundException extends \RuntimeException
{
    private string $entity;

    public function __construct(string $entity, $id = null)
    {
        $this->entity = $entity;
        $message = "{$entity} no encontrado" . ($id ? " (ID: {$id})" : "");
        parent::__construct($message, 404);
    }

    public function getEntity(): string
    {
        return $this->entity;
    }
}
