<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190928113829 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE qaitem_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE qacategory_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE qaitem (id INT NOT NULL, category_id INT NOT NULL, created_by_id INT NOT NULL, modified_by_id INT NOT NULL, question TEXT NOT NULL, answer TEXT DEFAULT NULL, created_time TIMESTAMP(0) WITH TIME ZONE NOT NULL, modified_time TIMESTAMP(0) WITH TIME ZONE NOT NULL, deleted BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_EBB1DB3812469DE2 ON qaitem (category_id)');
        $this->addSql('CREATE INDEX IDX_EBB1DB38B03A8386 ON qaitem (created_by_id)');
        $this->addSql('CREATE INDEX IDX_EBB1DB3899049ECE ON qaitem (modified_by_id)');
        $this->addSql('CREATE TABLE qacategory (id INT NOT NULL, name VARCHAR(255) NOT NULL, ordering INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE qaitem ADD CONSTRAINT FK_EBB1DB3812469DE2 FOREIGN KEY (category_id) REFERENCES qacategory (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE qaitem ADD CONSTRAINT FK_EBB1DB38B03A8386 FOREIGN KEY (created_by_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE qaitem ADD CONSTRAINT FK_EBB1DB3899049ECE FOREIGN KEY (modified_by_id) REFERENCES app_users (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE qaitem DROP CONSTRAINT FK_EBB1DB3812469DE2');
        $this->addSql('DROP SEQUENCE qaitem_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE qacategory_id_seq CASCADE');
        $this->addSql('DROP TABLE qaitem');
        $this->addSql('DROP TABLE qacategory');
    }
}
