<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Photo;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GalleryFilterTest extends WebTestCase
{
    private static bool $seeded = false;

    protected function setUp(): void
    {
        if (!self::$seeded) {
            $kernel = self::bootKernel();
            $em = $kernel->getContainer()->get('doctrine')->getManager();
            self::seedTestData($em);
            self::$seeded = true;
            self::ensureKernelShutdown();
        }
    }

    public static function tearDownAfterClass(): void
    {
        $kernel = self::bootKernel();
        $em = $kernel->getContainer()->get('doctrine')->getManager();

        $em->createQuery('DELETE FROM App\Entity\Photo p WHERE p.location IN (:locations)')
            ->setParameter('locations', ['TestMountains', 'TestBeach', 'TestCity'])
            ->execute();
        $em->createQuery('DELETE FROM App\Entity\User u WHERE u.username IN (:names)')
            ->setParameter('names', ['test_photographer_a', 'test_photographer_b'])
            ->execute();

        self::ensureKernelShutdown();
        self::$seeded = false;
    }

    public function testGalleryPageLoads(): void
    {
        $client = self::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    public function testFilterByLocationReducesResults(): void
    {
        $client = self::createClient();

        $client->request('GET', '/');
        $allPhotos = $client->getCrawler()->filter('.photo-card')->count();

        $client->request('GET', '/?location=TestMountains');
        $filtered = $client->getCrawler()->filter('.photo-card')->count();

        $this->assertGreaterThan($filtered, $allPhotos);
        $this->assertGreaterThan(0, $filtered);
    }

    public function testFilterByUsernameShowsOnlyThatUser(): void
    {
        $client = self::createClient();
        $client->request('GET', '/?username=test_photographer_a');

        $authors = $client->getCrawler()->filter('.author-username')->each(
            fn($node) => $node->text()
        );

        $this->assertNotEmpty($authors);
        foreach ($authors as $author) {
            $this->assertSame('@test_photographer_a', $author);
        }
    }

    public function testFilterValuesArePreservedInForm(): void
    {
        $client = self::createClient();
        $client->request('GET', '/?location=TestMountains&camera=Nikon');

        $locationInput = $client->getCrawler()->filter('#filter-location')->attr('value');
        $cameraInput = $client->getCrawler()->filter('#filter-camera')->attr('value');

        $this->assertSame('TestMountains', $locationInput);
        $this->assertSame('Nikon', $cameraInput);
    }

    private static function seedTestData(EntityManagerInterface $em): void
    {
        $userA = new User();
        $userA->setUsername('test_photographer_a');
        $userA->setEmail('test_a@test.com');

        $userB = new User();
        $userB->setUsername('test_photographer_b');
        $userB->setEmail('test_b@test.com');

        $em->persist($userA);
        $em->persist($userB);

        $photosData = [
            ['user' => $userA, 'location' => 'TestMountains', 'camera' => 'Nikon D850'],
            ['user' => $userA, 'location' => 'TestMountains', 'camera' => 'Canon R5'],
            ['user' => $userB, 'location' => 'TestBeach', 'camera' => 'Sony A7'],
            ['user' => $userB, 'location' => 'TestCity', 'camera' => 'Nikon Z7'],
        ];

        foreach ($photosData as $data) {
            $photo = new Photo();
            $photo->setImageUrl('https://example.com/' . uniqid('test_', true) . '.jpg');
            $photo->setLocation($data['location']);
            $photo->setCamera($data['camera']);
            $photo->setUser($data['user']);
            $em->persist($photo);
        }

        $em->flush();
    }
}
