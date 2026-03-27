<?php
require_once __DIR__ . '/../../vendor/autoload.php';

// --- CAS 1 : requête POST → traitement BDD ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $nom      = trim($_POST['nom']      ?? '');
    $prenom   = trim($_POST['prenom']   ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

    if (!$nom || !$prenom || !$email || !$password) {
        echo json_encode(['success' => false, 'message' => 'Champs manquants.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email invalide.']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    try {
        $pdo = new PDO(
            'mysql:host=localhost;dbname=ta_base;charset=utf8',
            'ton_user',
            'ton_mot_de_passe',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $stmtCompte = $pdo->prepare("
            INSERT INTO compte (mot_de_passe, niveau_de_permission, role, email_publique)
            VALUES (:mdp, 1, 'etudiant', :email)
        ");
        $stmtCompte->execute([':mdp' => $hashedPassword, ':email' => $email]);
        $idCompte = $pdo->lastInsertId();

        $stmtEtudiant = $pdo->prepare("
            INSERT INTO etudiant (id_compte, nom, prenom, email_publique, niveau_de_permission, role, mot_de_passe)
            VALUES (:id_compte, :nom, :prenom, :email, 1, 'etudiant', :mdp)
        ");
        $stmtEtudiant->execute([
            ':id_compte' => $idCompte,
            ':nom'       => $nom,
            ':prenom'    => $prenom,
            ':email'     => $email,
            ':mdp'       => $hashedPassword,
        ]);

        echo json_encode(['success' => true]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur BDD : ' . $e->getMessage()]);
    }

    exit;
}

// --- CAS 2 : requête GET → affichage du formulaire Twig ---
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../../templates');
$twig   = new \Twig\Environment($loader, ['cache' => false]);

echo $twig->render('pages/creation-compte-etudiant.twig.html', [
    'currentPage' => 'creation-compte-etudiant',
]);