<?php

$dotenv = parse_ini_file(__DIR__ . '/../../.env.example');

if (!$dotenv) {
    die('Erreur chargement .env');
}

echo var_dump($dotenv);



$pdo = new PDO(
    "user=postgres.qgyrbfhdjoahwoxmkakm password=[NicoRaclo67];host=aws-1-eu-central-2.pooler.supabase.com;port=5432;dbname=postgres",
    $dotenv['DB_USER'],
    $dotenv['DB_PASSWORD'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Tu récupères tes annonces depuis la BDD
$stmt = $pdo->query("
    SELECT 
        o.id_offre,
        o.titre,
        e.nom AS entreprise,
        v.nom AS ville,
        v.code_postal,
        o.base_remuneration,
        o.date
    FROM Offre o
    JOIN Entreprise e ON o.id_entreprise_appartient = e.id_entreprise
    JOIN Ville v ON e.id_ville = v.id_ville
    ORDER BY o.date DESC
    LIMIT 5;
");

$annonces = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tu envoies à Twig
echo $twig->render('accueil.twig.html', [
    'annonces' => $annonces
]);