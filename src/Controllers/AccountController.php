<?php
namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;
use Grp5\ProjetWeb4All\Models\AccountModel;
use Grp5\ProjetWeb4All\Models\EleveModel;
use Grp5\ProjetWeb4All\Models\EntrepriseModel;

class AccountController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();

        $pdo         = $this->getPdo();
        $eleveModel  = new EleveModel($pdo);

        $user = $eleveModel->findByCompte($_SESSION['user_id'], $_SESSION['user_role']);

        $this->render('pages/compte.twig.html', [
            'user_nom'    => $user['nom'] ?? '',
            'user_prenom' => $user['prenom'] ?? '',
            'user_role'   => $_SESSION['user_role'],
            'user_email'  => $user['email_publique'] ?? '',
        ]);
    }

    public function edit(): void
    {
        $this->requireLogin();

        $pdo        = $this->getPdo();
        $eleveModel = new EleveModel($pdo);

        $user = $eleveModel->findByCompte($_SESSION['user_id'], $_SESSION['user_role']);

        $this->render('pages/modification-compte.twig.html', [
            'user_nom'    => $user['nom'] ?? '',
            'user_prenom' => $user['prenom'] ?? '',
            'user_role'   => $_SESSION['user_role'],
            'user_email'  => $user['email_publique'] ?? '',
        ]);
    }

    public function editValidation(): void
    {
        $this->requireLogin();

        $pdo          = $this->getPdo();
        $accountModel = new AccountModel($pdo);
        $eleveModel   = new EleveModel($pdo);

        $userId   = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];
        $nom      = trim($_POST['nom'] ?? '');
        $prenom   = trim($_POST['prenom'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $currentEmail = $accountModel->getEmailById($userId);

        if ($email !== $currentEmail) {
            $eleveModel->updateProfil($userId, $nom, $prenom, $email, $userRole);
            $accountModel->updateEmail($userId, $email);
        } else {
            $eleveModel->updateProfilSansEmail($userId, $nom, $prenom, $userRole);
        }

        if (!empty($password)) {
            $accountModel->updatePassword($userId, password_hash($password, PASSWORD_DEFAULT));
        }

        $_SESSION['user_email'] = $email;

        $this->render('pages/modification-compte-validation.twig.html', [
            'user_nom'    => $nom,
            'user_prenom' => $prenom,
            'user_role'   => $userRole,
        ]);
    }

    public function login(): void
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /?page=compte');
            exit;
        }

        $error = null;
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = "Veuillez remplir tous les champs.";
            } else {
                $accountModel = new AccountModel($this->getPdo());
                $user         = $accountModel->findByEmail($email);

                if ($user && password_verify($password, $user['mot_de_passe'])) {
                    $_SESSION['user_id']    = $user['id_compte'];
                    $_SESSION['user_email'] = $user['email_publique'];
                    $_SESSION['user_role']  = $user['role'];

                    header('Location: /?page=accueil');
                    exit;
                } else {
                    $error = "Email ou mot de passe incorrect.";
                }
            }
        }

        $this->render('pages/login.twig.html', [
            'error' => $error,
            'email' => $email,
        ]);
    }

    public function logoutConfirmation(): void
    {
        $this->requireLogin();
        $this->render('pages/deconnexion.twig.html');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        header('Location: /?page=login');
        exit;
    }

    public function deleteConfirmation(): void
    {
        $this->requireLogin();
        $this->render('pages/suppression-compte-1.twig.html');
    }

    public function delete(): void
    {
        $this->requireLogin();

        $pdo          = $this->getPdo();
        $accountModel = new AccountModel($pdo);
        $eleveModel   = new EleveModel($pdo);

        $userId   = $_SESSION['user_id'];
        $userRole = $_SESSION['user_role'];

        if ($userRole === 'etudiant') {
            $eleveModel->deleteByCandidatures($userId);
            $eleveModel->deleteFavoris($userId);
        }

        $eleveModel->deleteEleve($userId, 0); // supprime dans etudiant ou pilote
        $accountModel->delete($userId);

        $_SESSION = [];
        session_destroy();
        header('Location: /?page=login');
        exit;
    }

    public function mesEleves(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        $eleveModel = new EleveModel($this->getPdo());
        $eleves     = $eleveModel->getElevesByPilote($_SESSION['user_id']);

        $this->render('pages/mes-eleves.twig.html', [
            'user_role' => $_SESSION['user_role'],
            'eleves'    => $eleves,
        ]);
    }

    public function mesElevesDetail(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        $id_compte_etudiant = (int)($_GET['id'] ?? 0);
        if ($id_compte_etudiant === 0) {
            header('Location: /?page=mes-eleves');
            exit;
        }

        $pdo        = $this->getPdo();
        $eleveModel = new EleveModel($pdo);
        $eleve      = $eleveModel->findByCompte($id_compte_etudiant, 'etudiant');

        if (!$eleve) {
            header('Location: /?page=mes-eleves');
            exit;
        }

        $stmt = $pdo->prepare("
            SELECT candidature.*, annonce.titre, annonce.lieu, annonce.type, annonce.duree
            FROM candidature
            JOIN annonce ON candidature.id_offre = annonce.id_annonce
            WHERE candidature.id_compte = :id
        ");
        $stmt->execute([':id' => $id_compte_etudiant]);
        $candidatures = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->render('pages/mes-eleves-detail.twig.html', [
            'user_role'    => $_SESSION['user_role'],
            'eleve'        => $eleve,
            'candidatures' => $candidatures,
        ]);
    }

    public function mesElevesCreation(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        $error  = null;
        $succes = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom      = trim($_POST['nom'] ?? '');
            $prenom   = trim($_POST['prenom'] ?? '');
            $email    = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
                $error = "Tous les champs sont obligatoires.";
            } else {
                $pdo          = $this->getPdo();
                $accountModel = new AccountModel($pdo);
                $eleveModel   = new EleveModel($pdo);

                $hash    = password_hash($password, PASSWORD_DEFAULT);
                $newId   = $accountModel->getNextId();

                $accountModel->create($newId, $email, $hash, 'etudiant');
                $eleveModel->createEtudiant($newId, $nom, $prenom, $email, $_SESSION['user_id']);

                $succes = "Le compte étudiant de $prenom $nom a été créé avec succès.";
            }
        }

        $this->render('pages/mes-eleves-creation.twig.html', [
            'user_role' => $_SESSION['user_role'],
            'error'     => $error,
            'succes'    => $succes,
        ]);
    }

    public function gestionEleves(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }
        $this->render('pages/gestion-eleves.twig.html', ['user_role' => $_SESSION['user_role']]);
    }

    public function modificationEleve(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        $pdo          = $this->getPdo();
        $accountModel = new AccountModel($pdo);
        $eleveModel   = new EleveModel($pdo);

        $error             = null;
        $succes            = null;
        $eleve_selectionne = null;
        $eleves            = $eleveModel->getElevesByPilote($_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'selection') {
            $eleve_selectionne = $eleveModel->findEleveById((int)$_POST['id_compte'], $_SESSION['user_id']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'modification') {
            $id_compte = (int)$_POST['id_compte'];
            $nom       = trim($_POST['nom'] ?? '');
            $prenom    = trim($_POST['prenom'] ?? '');
            $email     = trim($_POST['email'] ?? '');

            if (empty($nom) || empty($prenom) || empty($email)) {
                $error             = "Tous les champs sont obligatoires.";
                $eleve_selectionne = $eleveModel->findByCompte($id_compte, 'etudiant');
            } else {
                $currentEmail = $accountModel->getEmailById($id_compte);
                $eleveModel->updateProfil($id_compte, $nom, $prenom, $email, 'etudiant');
                if ($email !== $currentEmail) {
                    $accountModel->updateEmail($id_compte, $email);
                }
                $succes = "Compte de $prenom $nom mis à jour avec succès !";
            }
        }

        $this->render('pages/modification-eleve.twig.html', [
            'user_role'         => $_SESSION['user_role'],
            'eleves'            => $eleves,
            'eleve_selectionne' => $eleve_selectionne,
            'error'             => $error,
            'succes'            => $succes,
        ]);
    }

    public function suppressionEleve(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }

        $pdo        = $this->getPdo();
        $eleveModel = new EleveModel($pdo);
        $accountModel = new AccountModel($pdo);

        $succes            = null;
        $eleve_selectionne = null;
        $eleves            = $eleveModel->getElevesByPilote($_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'selection') {
            $eleve_selectionne = $eleveModel->findEleveById((int)$_POST['id_compte'], $_SESSION['user_id']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'suppression') {
            $id_compte = (int)$_POST['id_compte'];

            $eleveModel->deleteByCandidatures($id_compte);
            $eleveModel->deleteFavoris($id_compte);
            $eleveModel->deleteEleve($id_compte, $_SESSION['user_id']);
            $accountModel->delete($id_compte);

            $succes = "Compte étudiant supprimé avec succès !";
            $eleves = $eleveModel->getElevesByPilote($_SESSION['user_id']);
        }

        $this->render('pages/suppression-eleve.twig.html', [
            'user_role'         => $_SESSION['user_role'],
            'eleves'            => $eleves,
            'eleve_selectionne' => $eleve_selectionne,
            'succes'            => $succes,
        ]);
    }

    public function entreprisesGestion(): void
    {
        $this->requireLogin();
        if ($_SESSION['user_role'] !== 'pilote') {
            header('Location: /?page=compte');
            exit;
        }
        $this->render('pages/entreprises-gestion.twig.html', ['user_role' => $_SESSION['user_role']]);
    }

    public function creationEntreprise(): void
{
    $this->requireLogin();
    if ($_SESSION['user_role'] !== 'pilote') {
        header('Location: /?page=compte');
        exit;
    }

    $error  = null;
    $succes = null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom         = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $email       = trim($_POST['email'] ?? '');
        $telephone   = trim($_POST['telephone'] ?? '');
        $secteur     = trim($_POST['secteur'] ?? '');
        $numero_rue  = trim($_POST['numero_rue'] ?? '');
        $nom_rue     = trim($_POST['nom_rue'] ?? '');
        $nom_ville   = trim($_POST['nom_ville'] ?? '');
        $code_postal = trim($_POST['code_postal'] ?? '');

        if (empty($nom) || empty($email) || empty($secteur) || empty($nom_rue) || empty($nom_ville)) {
            $error = "Tous les champs obligatoires doivent être remplis.";
        } else {
            $entrepriseModel = new EntrepriseModel($this->getPdo());

            $ville    = $entrepriseModel->findVille($nom_ville, $code_postal);
            $id_ville = $ville ? $ville['id_ville'] : $entrepriseModel->createVille($nom_ville, $code_postal);
            $idAdresse    = $entrepriseModel->createAdresse($numero_rue, $nom_rue, $id_ville);
            $idEntreprise = $entrepriseModel->getNextId();

            $entrepriseModel->create($idEntreprise, $nom, $description, $email, $telephone, $secteur, $_SESSION['user_id'], $idAdresse);

            $succes = "L'entreprise \"$nom\" a été créée avec succès !";
        }
    }

    $this->render('pages/creation-entreprise.twig.html', [
        'user_role' => $_SESSION['user_role'],
        'error'     => $error,
        'succes'    => $succes,
    ]);
}

public function modificationEntreprise(): void
{
    $this->requireLogin();
    if ($_SESSION['user_role'] !== 'pilote') {
        header('Location: /?page=compte');
        exit;
    }

    $entrepriseModel         = new EntrepriseModel($this->getPdo());
    $error                   = null;
    $succes                  = null;
    $entreprise_selectionnee = null;
    $entreprises             = $entrepriseModel->getAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'selection') {
        $entreprise_selectionnee = $entrepriseModel->findById((int)$_POST['id_entreprise']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'modification') {
        $id_entreprise = (int)($_POST['id_entreprise'] ?? 0);
        $nom           = trim($_POST['nom'] ?? '');
        $description   = trim($_POST['description'] ?? '');
        $email         = trim($_POST['email'] ?? '');
        $telephone     = trim($_POST['telephone'] ?? '');
        $secteur       = trim($_POST['secteur'] ?? '');

        if (empty($nom) || empty($email) || empty($secteur)) {
            $error                   = "Tous les champs obligatoires doivent être remplis.";
            $entreprise_selectionnee = $entrepriseModel->findById($id_entreprise);
        } else {
            $entrepriseModel->update($id_entreprise, $nom, $description, $email, $telephone, $secteur);
            $succes = "Entreprise mise à jour avec succès !";
        }
    }

    $this->render('pages/modification-entreprise.twig.html', [
        'user_role'               => $_SESSION['user_role'],
        'entreprises'             => $entreprises,
        'entreprise_selectionnee' => $entreprise_selectionnee,
        'error'                   => $error,
        'succes'                  => $succes,
    ]);
}

public function suppressionEntreprise(): void
{
    $this->requireLogin();
    if ($_SESSION['user_role'] !== 'pilote') {
        header('Location: /?page=compte');
        exit;
    }

    $entrepriseModel         = new EntrepriseModel($this->getPdo());
    $succes                  = null;
    $entreprise_selectionnee = null;
    $entreprises             = $entrepriseModel->getAll();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'selection') {
        $entreprise_selectionnee = $entrepriseModel->findById((int)$_POST['id_entreprise']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['etape'] ?? '') === 'suppression') {
        $id_entreprise = (int)$_POST['id_entreprise'];

        $entrepriseModel->deleteCandidaturesLiees($id_entreprise);
        $entrepriseModel->deleteAnnonces($id_entreprise);
        $entrepriseModel->delete($id_entreprise);

        $succes      = "Entreprise et ses annonces supprimées avec succès !";
        $entreprises = $entrepriseModel->getAll();
    }

    $this->render('pages/suppression-entreprise.twig.html', [
        'user_role'               => $_SESSION['user_role'],
        'entreprises'             => $entreprises,
        'entreprise_selectionnee' => $entreprise_selectionnee,
        'succes'                  => $succes,
    ]);
}
}