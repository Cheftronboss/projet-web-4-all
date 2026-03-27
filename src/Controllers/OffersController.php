<?php
namespace Grp5\ProjetWeb4All\Controllers;


use Grp5\ProjetWeb4All\Core\Controller;

class OffersController extends Controller
{
    public function index(): void
    {
        $this->render('pages/annonces.twig.html');
    }
}