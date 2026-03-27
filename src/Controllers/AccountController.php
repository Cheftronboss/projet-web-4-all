<?php

namespace Grp5\ProjetWeb4All\Controllers;

use Grp5\ProjetWeb4All\Core\Controller;

class AccountController extends Controller
{
    public function index(): void
    {
        session_start();

        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=login');
            exit;
        }

        $this->render('pages/compte.twig.html', [
            'user_nom'    => $_SESSION['user_nom'],
            'user_prenom' => $_SESSION['user_prenom'],
            'user_role'   => $_SESSION['user_role'], // 'etudiant', 'pilote'
        ]);
    }
}