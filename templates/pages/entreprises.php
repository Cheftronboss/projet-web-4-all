<?php

require_once __DIR__ . '/../../src/EntreprisesDB.php';
require_once __DIR__ . '/../../src/paginationEntreprises.php';

// $twig est hérité du scope de index.php via include — pas besoin de global
$pagination = new Pagination($entreprises, 8);

echo $twig->render('pages/entreprises.twig.html', [
    'currentPage'   => 'entreprises',
    'entreprises'   => $pagination->getCurrentEntreprises(),
    'navLinks'      => $pagination->getNavigationLinks(),
]);

?>