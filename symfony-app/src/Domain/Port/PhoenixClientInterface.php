<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Dto\PhoenixPhotoCollection;

interface PhoenixClientInterface
{
    /**
     * @throws \App\Domain\Exception\PhoenixApiException
     */
    public function fetchPhotos(string $token): PhoenixPhotoCollection;
}
