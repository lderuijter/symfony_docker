<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class HomeController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $user = $this->security->getUser();
        if($user != null) {
            $email = $user->getEmail();
        } else {
            return $this->redirectToRoute('app_login');
        }
        return $this->render('home/index.html.twig', [
            'email' => $email,
        ]);
    }
}
