<?php

declare(strict_types=1);

namespace App\CQRS;

interface CommandHandlerInterface
{
    public static function getHandledCommand(): string;
}
