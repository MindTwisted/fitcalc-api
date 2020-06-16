<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200616161642 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE eating_scheme (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, is_default TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_59654F41A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE eating_scheme_detail (id INT AUTO_INCREMENT NOT NULL, eating_scheme_id INT NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_2C90E6715D20E483 (eating_scheme_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE eating_scheme ADD CONSTRAINT FK_59654F41A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE eating_scheme_detail ADD CONSTRAINT FK_2C90E6715D20E483 FOREIGN KEY (eating_scheme_id) REFERENCES eating_scheme (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE eating_scheme_detail DROP FOREIGN KEY FK_2C90E6715D20E483');
        $this->addSql('DROP TABLE eating_scheme');
        $this->addSql('DROP TABLE eating_scheme_detail');
    }
}
