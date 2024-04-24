<?php

namespace App\Form\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class ClientUniqueDni extends Constraint
{
    public string $message = 'Ya existe un cliente con este dni "{{ string }}".';

    #[HasNamedArguments]
    public function __construct(
        public bool $edit,
        public string $mode,
        array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy(): string
    {
        return static::class.'Validator';
    }
}