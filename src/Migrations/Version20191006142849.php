<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191006142849 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE TABLE user_research_group (user_id INT NOT NULL, research_group_id INT NOT NULL, PRIMARY KEY(user_id, research_group_id))');
        $this->addSql('CREATE INDEX IDX_80C3F978A76ED395 ON user_research_group (user_id)');
        $this->addSql('CREATE INDEX IDX_80C3F9783AF8E8D ON user_research_group (research_group_id)');
        $this->addSql('ALTER TABLE user_research_group ADD CONSTRAINT FK_80C3F978A76ED395 FOREIGN KEY (user_id) REFERENCES app_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_research_group ADD CONSTRAINT FK_80C3F9783AF8E8D FOREIGN KEY (research_group_id) REFERENCES research_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE research_group_members');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE TABLE research_group_members (research_group_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(research_group_id, user_id))');
        $this->addSql('CREATE INDEX idx_2e5c42533af8e8d ON research_group_members (research_group_id)');
        $this->addSql('CREATE INDEX idx_2e5c4253a76ed395 ON research_group_members (user_id)');
        $this->addSql('ALTER TABLE research_group_members ADD CONSTRAINT fk_2e5c42533af8e8d FOREIGN KEY (research_group_id) REFERENCES research_group (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE research_group_members ADD CONSTRAINT fk_2e5c4253a76ed395 FOREIGN KEY (user_id) REFERENCES app_users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE user_research_group');
    }
}
