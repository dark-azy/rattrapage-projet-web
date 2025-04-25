<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401142725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE administrateur (id INT NOT NULL, nom_admin VARCHAR(255) NOT NULL, prenom_admin VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE adresse (id INT AUTO_INCREMENT NOT NULL, rue VARCHAR(255) NOT NULL, code_postal VARCHAR(10) NOT NULL, ville VARCHAR(255) NOT NULL, pays VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE campus (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE candidature (id INT AUTO_INCREMENT NOT NULL, etudiant_id INT NOT NULL, offre_de_stage_id INT NOT NULL, date_candidature DATETIME NOT NULL, date_reponse DATETIME DEFAULT NULL, statut VARCHAR(255) NOT NULL, INDEX IDX_E33BD3B8DDEAB1A3 (etudiant_id), INDEX IDX_E33BD3B81D3C911F (offre_de_stage_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE competence (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE entreprise (id INT AUTO_INCREMENT NOT NULL, adresse_id INT NOT NULL, nom VARCHAR(255) NOT NULL, secteur VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, telephone VARCHAR(20) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, logo VARCHAR(255) DEFAULT NULL, INDEX IDX_D19FA604DE7DC5C (adresse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE etudiant (id INT NOT NULL, promotion_id INT NOT NULL, adresse_id INT DEFAULT NULL, nom_etudiant VARCHAR(255) NOT NULL, prenom_etudiant VARCHAR(255) NOT NULL, statut VARCHAR(50) NOT NULL, INDEX IDX_717E22E3139DF194 (promotion_id), INDEX IDX_717E22E34DE7DC5C (adresse_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE offre_de_stage (id_offre INT AUTO_INCREMENT NOT NULL, id_pilote INT NOT NULL, id_entreprise INT NOT NULL, titre_offre VARCHAR(255) NOT NULL, description_offre LONGTEXT NOT NULL, competences_requises LONGTEXT NOT NULL, duree_stage INT NOT NULL, date_debut_stage DATE NOT NULL, date_fin_stage DATE NOT NULL, salaire DOUBLE PRECISION NOT NULL, statut_offre VARCHAR(50) NOT NULL, INDEX IDX_2A4937874C05E130 (id_pilote), INDEX IDX_2A493787A8937AB7 (id_entreprise), PRIMARY KEY(id_offre)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pilote_de_promotion (id INT NOT NULL, nom_pilote VARCHAR(255) NOT NULL, prenom_pilote VARCHAR(255) NOT NULL, email_pilote VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE promotion (id INT AUTO_INCREMENT NOT NULL, pilote_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, campus VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, INDEX IDX_C11D7DD1F510AAE9 (pilote_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE secteur_dactivite (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE administrateur ADD CONSTRAINT FK_32EB52E8BF396750 FOREIGN KEY (id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B8DDEAB1A3 FOREIGN KEY (etudiant_id) REFERENCES etudiant (id)');
        $this->addSql('ALTER TABLE candidature ADD CONSTRAINT FK_E33BD3B81D3C911F FOREIGN KEY (offre_de_stage_id) REFERENCES offre_de_stage (id_offre)');
        $this->addSql('ALTER TABLE entreprise ADD CONSTRAINT FK_D19FA604DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id)');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E3139DF194 FOREIGN KEY (promotion_id) REFERENCES promotion (id)');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E34DE7DC5C FOREIGN KEY (adresse_id) REFERENCES adresse (id)');
        $this->addSql('ALTER TABLE etudiant ADD CONSTRAINT FK_717E22E3BF396750 FOREIGN KEY (id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE offre_de_stage ADD CONSTRAINT FK_2A4937874C05E130 FOREIGN KEY (id_pilote) REFERENCES pilote_de_promotion (id)');
        $this->addSql('ALTER TABLE offre_de_stage ADD CONSTRAINT FK_2A493787A8937AB7 FOREIGN KEY (id_entreprise) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE pilote_de_promotion ADD CONSTRAINT FK_2320087EBF396750 FOREIGN KEY (id) REFERENCES `user` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE promotion ADD CONSTRAINT FK_C11D7DD1F510AAE9 FOREIGN KEY (pilote_id) REFERENCES pilote_de_promotion (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE administrateur DROP FOREIGN KEY FK_32EB52E8BF396750');
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B8DDEAB1A3');
        $this->addSql('ALTER TABLE candidature DROP FOREIGN KEY FK_E33BD3B81D3C911F');
        $this->addSql('ALTER TABLE entreprise DROP FOREIGN KEY FK_D19FA604DE7DC5C');
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E3139DF194');
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E34DE7DC5C');
        $this->addSql('ALTER TABLE etudiant DROP FOREIGN KEY FK_717E22E3BF396750');
        $this->addSql('ALTER TABLE offre_de_stage DROP FOREIGN KEY FK_2A4937874C05E130');
        $this->addSql('ALTER TABLE offre_de_stage DROP FOREIGN KEY FK_2A493787A8937AB7');
        $this->addSql('ALTER TABLE pilote_de_promotion DROP FOREIGN KEY FK_2320087EBF396750');
        $this->addSql('ALTER TABLE promotion DROP FOREIGN KEY FK_C11D7DD1F510AAE9');
        $this->addSql('DROP TABLE administrateur');
        $this->addSql('DROP TABLE adresse');
        $this->addSql('DROP TABLE campus');
        $this->addSql('DROP TABLE candidature');
        $this->addSql('DROP TABLE competence');
        $this->addSql('DROP TABLE entreprise');
        $this->addSql('DROP TABLE etudiant');
        $this->addSql('DROP TABLE offre_de_stage');
        $this->addSql('DROP TABLE pilote_de_promotion');
        $this->addSql('DROP TABLE promotion');
        $this->addSql('DROP TABLE secteur_dactivite');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
