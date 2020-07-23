<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191006142105 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE research_group_members (research_group_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(research_group_id, user_id))');
        $this->addSql('CREATE INDEX IDX_2E5C42533AF8E8D ON research_group_members (research_group_id)');
        $this->addSql('CREATE INDEX IDX_2E5C4253A76ED395 ON research_group_members (user_id)');
        $this->addSql('ALTER TABLE research_group_members ADD CONSTRAINT FK_2E5C42533AF8E8D FOREIGN KEY (research_group_id) REFERENCES research_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE research_group_members ADD CONSTRAINT FK_2E5C4253A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP TABLE research_group_members');
    }
}
