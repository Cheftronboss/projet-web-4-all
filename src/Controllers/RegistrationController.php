<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class RegistrationController extends Controller
{
    public function index(): void
    {
        $type = isset($_GET['type']) && $_GET['type'] === 'pilote' ? 'pilote' : 'etudiant';

        $this->render('pages/creation-compte.twig.html', [
            'type' => $type,
        ]);
    }
}