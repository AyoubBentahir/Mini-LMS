<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260108224531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE module_completion (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, module_id INT NOT NULL, completed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_AD331CE3A76ED395 (user_id), INDEX IDX_AD331CE3AFC2B591 (module_id), UNIQUE INDEX UNIQ_COMPLETION_USER_MODULE (user_id, module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE module_completion ADD CONSTRAINT FK_AD331CE3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE module_completion ADD CONSTRAINT FK_AD331CE3AFC2B591 FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('ALTER TABLE course ADD category VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE module_completion DROP FOREIGN KEY FK_AD331CE3A76ED395');
        $this->addSql('ALTER TABLE module_completion DROP FOREIGN KEY FK_AD331CE3AFC2B591');
        $this->addSql('DROP TABLE module_completion');
        $this->addSql('ALTER TABLE course DROP category');
    }
}
