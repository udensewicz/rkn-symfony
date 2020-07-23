<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190501164047 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE menu_category ADD name VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE menu_category ADD ordering INT NOT NULL');
        $this->addSql('ALTER TABLE menu_category ADD deleted BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE menu_category ADD link VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE menu_category DROP name');
        $this->addSql('ALTER TABLE menu_category DROP ordering');
        $this->addSql('ALTER TABLE menu_category DROP deleted');
        $this->addSql('ALTER TABLE menu_category DROP link');
    }
}
