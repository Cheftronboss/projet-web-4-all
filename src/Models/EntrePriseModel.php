<?php
namespace Grp5\ProjetWeb4All\Models;

class EntrepriseModel
{
    private \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAll(): array
    {
        return $this->pdo->query("SELECT * FROM entreprise")->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->pdo->prepare("SELECT * FROM entreprise WHERE id_entreprise = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create(int $id, string $nom, string $description, string $email, string $telephone, string $secteur, int $idCompte, int $idAdresse): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO entreprise (id_entreprise, nom, description, email, telephone, secteur, id_compte, id_adresse)
            VALUES (:id, :nom, :description, :email, :telephone, :secteur, :id_compte, :id_adresse)
        ");
        $stmt->execute([
            ':id'          => $id,
            ':nom'         => $nom,
            ':description' => $description,
            ':email'       => $email,
            ':telephone'   => $telephone,
            ':secteur'     => $secteur,
            ':id_compte'   => $idCompte,
            ':id_adresse'  => $idAdresse,
        ]);
    }

    public function update(int $id, string $nom, string $description, string $email, string $telephone, string $secteur): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE entreprise 
            SET nom = :nom, description = :description, email = :email, telephone = :telephone, secteur = :secteur
            WHERE id_entreprise = :id
        ");
        $stmt->execute([
            ':nom'         => $nom,
            ':description' => $description,
            ':email'       => $email,
            ':telephone'   => $telephone,
            ':secteur'     => $secteur,
            ':id'          => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM entreprise WHERE id_entreprise = :id");
        $stmt->execute([':id' => $id]);
    }

    public function deleteAnnonces(int $idEntreprise): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM annonce WHERE id_entreprise_appartient = :id");
        $stmt->execute([':id' => $idEntreprise]);
    }

    public function deleteCandidaturesLiees(int $idEntreprise): void
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM candidature 
            WHERE id_offre IN (
                SELECT id_annonce FROM annonce WHERE id_entreprise_appartient = :id
            )
        ");
        $stmt->execute([':id' => $idEntreprise]);
    }

    public function getNextId(): int
    {
        return (int) $this->pdo->query("SELECT COALESCE(MAX(id_entreprise), 0) + 1 FROM entreprise")->fetchColumn();
    }

    // ── Ville ──

    public function findVille(string $nom, string $codePostal): array|false
    {
        $stmt = $this->pdo->prepare("SELECT id_ville FROM ville WHERE nom = :nom AND code_postal = :cp");
        $stmt->execute([':nom' => $nom, ':cp' => $codePostal]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function createVille(string $nom, string $codePostal): int
    {
        $id = (int) $this->pdo->query("SELECT COALESCE(MAX(id_ville), 0) + 1 FROM ville")->fetchColumn();
        $stmt = $this->pdo->prepare("INSERT INTO ville (id_ville, nom, code_postal) VALUES (:id, :nom, :cp)");
        $stmt->execute([':id' => $id, ':nom' => $nom, ':cp' => $codePostal]);
        return $id;
    }

    // ── Adresse ──

    public function createAdresse(string $numero, string $rue, int $idVille): int
    {
        $id = (int) $this->pdo->query("SELECT COALESCE(MAX(id_adresse), 0) + 1 FROM adresse")->fetchColumn();
        $stmt = $this->pdo->prepare("INSERT INTO adresse (id_adresse, numero_rue, nom_rue, id_ville) VALUES (:id, :numero, :rue, :ville)");
        $stmt->execute([':id' => $id, ':numero' => $numero, ':rue' => $rue, ':ville' => $idVille]);
        return $id;
    }
}