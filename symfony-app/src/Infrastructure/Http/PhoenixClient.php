<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Domain\Dto\PhoenixPhotoCollection;
use App\Domain\Exception\PhoenixApiException;
use App\Domain\Port\PhoenixClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PhoenixClient implements PhoenixClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl,
    ) {}

    public function fetchPhotos(string $token): PhoenixPhotoCollection
    {
        try {
            $response = $this->httpClient->request('GET', $this->baseUrl . '/api/photos', [
                'headers' => ['access-token' => $token],
            ]);

            if ($response->getStatusCode() === 401) {
                throw PhoenixApiException::unauthorized();
            }

            $data = $response->toArray();

            return PhoenixPhotoCollection::fromArray($data['photos'] ?? []);
        } catch (PhoenixApiException $e) {
            throw $e;
        } catch (TransportExceptionInterface $e) {
            throw PhoenixApiException::connectionError($e->getMessage());
        }
    }
}
