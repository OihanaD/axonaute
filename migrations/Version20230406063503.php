<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230406063503 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE target (id INT AUTO_INCREMENT NOT NULL, target_api_id INT DEFAULT NULL, value INT NOT NULL, date_end DATE NOT NULL, kpi VARCHAR(255) NOT NULL, INDEX IDX_466F2FFC5D9CDE88 (target_api_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE target ADD CONSTRAINT FK_466F2FFC5D9CDE88 FOREIGN KEY (target_api_id) REFERENCES api_information (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE target DROP FOREIGN KEY FK_466F2FFC5D9CDE88');
        $this->addSql('DROP TABLE target');
    }
}
