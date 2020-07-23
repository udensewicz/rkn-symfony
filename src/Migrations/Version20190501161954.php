<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190501161954 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE menu_category_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE menu_category (id INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE menu_item DROP parent_id');
        $this->addSql('ALTER TABLE menu_item DROP is_parent');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE menu_category_id_seq CASCADE');
        $this->addSql('DROP TABLE menu_category');
        $this->addSql('ALTER TABLE menu_item ADD parent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE menu_item ADD is_parent BOOLEAN DEFAULT NULL');
    }
}
