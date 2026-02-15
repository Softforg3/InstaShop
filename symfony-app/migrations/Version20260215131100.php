<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260215131100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add unique constraint on likes (user_id, photo_id)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE FROM likes l1 USING likes l2 WHERE l1.id > l2.id AND l1.user_id = l2.user_id AND l1.photo_id = l2.photo_id');
        $this->addSql('CREATE UNIQUE INDEX unique_user_photo_like ON likes (user_id, photo_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX unique_user_photo_like');
    }
}
