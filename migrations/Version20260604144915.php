<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260604144915 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE forecast (id INT AUTO_INCREMENT NOT NULL, collected_date DATE NOT NULL, target_date DATE NOT NULL, horizon INT NOT NULL, temp_max DOUBLE PRECISION DEFAULT NULL, temp_min DOUBLE PRECISION DEFAULT NULL, precipitation DOUBLE PRECISION DEFAULT NULL, rain_probability INT DEFAULT NULL, wind_speed DOUBLE PRECISION DEFAULT NULL, humidity INT DEFAULT NULL, created_at DATETIME NOT NULL, city_id INT NOT NULL, weather_source_id INT NOT NULL, INDEX IDX_2A9C78448BAC62AF (city_id), INDEX IDX_2A9C7844BFB29D52 (weather_source_id), UNIQUE INDEX unique_forecast (city_id, weather_source_id, collected_date, horizon), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE weather_source (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, color VARCHAR(7) NOT NULL, max_horizon_days INT NOT NULL, is_active TINYINT NOT NULL, UNIQUE INDEX UNIQ_B1AD5915989D9B62 (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE forecast ADD CONSTRAINT FK_2A9C78448BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $this->addSql('ALTER TABLE forecast ADD CONSTRAINT FK_2A9C7844BFB29D52 FOREIGN KEY (weather_source_id) REFERENCES weather_source (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE forecast DROP FOREIGN KEY FK_2A9C78448BAC62AF');
        $this->addSql('ALTER TABLE forecast DROP FOREIGN KEY FK_2A9C7844BFB29D52');
        $this->addSql('DROP TABLE forecast');
        $this->addSql('DROP TABLE weather_source');
    }
}
