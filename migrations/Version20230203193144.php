<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230203193144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event DROP event_category, CHANGE link_form link_form VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE task CHANGE start_time start_time DATETIME DEFAULT NULL, CHANGE end_time end_time DATETIME DEFAULT NULL, CHANGE state_request state_request VARCHAR(255) DEFAULT NULL, CHANGE extra_time extra_time DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(180) NOT NULL, CHANGE roles roles JSON NOT NULL, CHANGE password password VARCHAR(255) NOT NULL, CHANGE full_name full_name VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL, CHANGE address address VARCHAR(255) NOT NULL, CHANGE phone_number phone_number INT NOT NULL, CHANGE dni dni VARCHAR(255) NOT NULL, CHANGE reg_date reg_date DATETIME NOT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD event_category VARCHAR(255) DEFAULT \'NULL\', CHANGE link_form link_form VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE task CHANGE start_time start_time DATETIME DEFAULT \'NULL\', CHANGE end_time end_time DATETIME DEFAULT \'NULL\', CHANGE state_request state_request VARCHAR(255) DEFAULT \'NULL\', CHANGE extra_time extra_time DATETIME DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE user CHANGE username username VARCHAR(180) DEFAULT \'NULL\', CHANGE roles roles LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE password password VARCHAR(255) DEFAULT \'NULL\', CHANGE full_name full_name VARCHAR(255) DEFAULT \'NULL\', CHANGE email email VARCHAR(255) DEFAULT \'NULL\', CHANGE address address VARCHAR(255) DEFAULT \'NULL\', CHANGE phone_number phone_number INT DEFAULT NULL, CHANGE dni dni VARCHAR(255) DEFAULT \'NULL\', CHANGE reg_date reg_date DATETIME DEFAULT \'NULL\'');
    }
}
