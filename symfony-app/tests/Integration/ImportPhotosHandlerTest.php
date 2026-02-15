<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\CQRS\Command\ImportPhotos\ImportPhotosCommand;
use App\CQRS\Command\ImportPhotos\ImportPhotosHandler;
use App\Domain\Dto\PhoenixPhotoCollection;
use App\Domain\Dto\PhoenixPhotoDto;
use App\Domain\Exception\InvalidTokenException;
use App\Domain\Port\PhoenixClientInterface;
use App\Entity\Photo;
use App\Entity\User;
use App\Repository\PhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImportPhotosHandlerTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private PhotoRepository $photoRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->photoRepository = self::getContainer()->get(PhotoRepository::class);

        $this->em->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->em->getConnection()->rollBack();

        parent::tearDown();
    }

    public function testImportsNewPhotosFromPhoenixApi(): void
    {
        $user = $this->createUser('test_user', 'test_token_123');
        $this->em->flush();

        $phoenixClient = $this->createMock(PhoenixClientInterface::class);
        $phoenixClient->method('fetchPhotos')
            ->willReturn(new PhoenixPhotoCollection(
                new PhoenixPhotoDto(1, 'https://example.com/photo1.jpg'),
                new PhoenixPhotoDto(2, 'https://example.com/photo2.jpg'),
            ));

        $handler = new ImportPhotosHandler($this->em, $phoenixClient, $this->photoRepository);
        $imported = $handler->handle(new ImportPhotosCommand($user->getId()));

        $this->assertSame(2, $imported);
    }

    public function testSkipsAlreadyImportedPhotos(): void
    {
        $user = $this->createUser('test_user2', 'test_token_456');

        $existingPhoto = new Photo();
        $existingPhoto->setImageUrl('https://example.com/existing.jpg');
        $existingPhoto->setUser($user);
        $this->em->persist($existingPhoto);
        $this->em->flush();

        $phoenixClient = $this->createMock(PhoenixClientInterface::class);
        $phoenixClient->method('fetchPhotos')
            ->willReturn(new PhoenixPhotoCollection(
                new PhoenixPhotoDto(1, 'https://example.com/existing.jpg'),
                new PhoenixPhotoDto(2, 'https://example.com/new.jpg'),
            ));

        $handler = new ImportPhotosHandler($this->em, $phoenixClient, $this->photoRepository);
        $imported = $handler->handle(new ImportPhotosCommand($user->getId()));

        $this->assertSame(1, $imported);
    }

    public function testThrowsExceptionWhenTokenMissing(): void
    {
        $user = $this->createUser('test_user3', null);
        $this->em->flush();

        $phoenixClient = $this->createMock(PhoenixClientInterface::class);
        $handler = new ImportPhotosHandler($this->em, $phoenixClient, $this->photoRepository);

        $this->expectException(InvalidTokenException::class);
        $handler->handle(new ImportPhotosCommand($user->getId()));
    }

    private function createUser(string $username, ?string $phoenixToken): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username . '@test.com');
        $user->setPhoenixApiToken($phoenixToken);
        $this->em->persist($user);

        return $user;
    }
}
