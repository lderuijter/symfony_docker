<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\EditUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class EditUserController extends AbstractController
{
    #[Route('/edit/user', name: 'app_edit_user')]
    public function index(Request $request,
                          UserPasswordHasherInterface $userPasswordHasher,
                          EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $form = $this->createForm(EditUserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setEmail($form->get('email')->getData());
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->flush();
            return $this->redirectToRoute('app_home');
        }

        return $this->render('edit_user/index.html.twig', [
            'editUserForm' => $form->createView(),
        ]);
    }
}
