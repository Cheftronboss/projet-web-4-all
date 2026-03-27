<?php
namespace Grp5\ProjetWeb4All\Controllers;


use Grp5\ProjetWeb4All\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('pages/accueil.twig.html');
    }
}