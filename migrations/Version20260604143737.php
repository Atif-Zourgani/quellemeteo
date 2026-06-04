<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260604143737 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE actual_weather (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, temp_max DOUBLE PRECISION NOT NULL, temp_min DOUBLE PRECISION NOT NULL, precipitation DOUBLE PRECISION NOT NULL, wind_speed DOUBLE PRECISION NOT NULL, humidity INT NOT NULL, collected_at DATETIME NOT NULL, city_id INT NOT NULL, INDEX IDX_2A115B68BAC62AF (city_id), UNIQUE INDEX unique_city_date (city_id, date), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, zone VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_2D5B0234989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE actual_weather ADD CONSTRAINT FK_2A115B68BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE actual_weather DROP FOREIGN KEY FK_2A115B68BAC62AF');
        $this->addSql('DROP TABLE actual_weather');
        $this->addSql('DROP TABLE city');
    }
}
