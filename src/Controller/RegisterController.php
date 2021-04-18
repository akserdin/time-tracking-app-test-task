<?php
/*
 * Created by Dmytro Zolotar. on 17/04/2021.
 * Copyright (c) 2021. All rights reserved.
 */

namespace App\Controller;


use App\Entity\User;
use App\Security\LoginFormAuthenticator;
use App\Service\UserStoreService;
use App\Traits\HandleErrorsTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegisterController extends AbstractController
{
    use HandleErrorsTrait;

    /**
     * @Route("/register", name="app_user_create", methods={"GET"})
     */
    public function registrationForm(): Response
    {
        return $this->render('register.html.twig');
    }

    /**
     * @Route("/register", name="app_user_store", methods={"POST"})
     * @param Request $request
     */
    public function register(
        Request $request,
        UserStoreService $userStoreService,
        GuardAuthenticatorHandler $guardHandler,
        LoginFormAuthenticator $formAuthenticator
    )
    {
        $this->validateRequest($request);

        if ($this->hasErrors()) {
            return $this->render('register.html.twig', [
                'errors' => $this->errors,
                'email' => $request->request->get('email')
            ]);
        }

        $user = $userStoreService->storeUser($request);

        return $guardHandler->authenticateUserAndHandleSuccess(
            $user,
            $request,
            $formAuthenticator,
            'main'
        );
    }

    private function validateRequest(Request $request)
    {
        if (! filter_var($request->request->get('email'), FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = 'E-mail address is not valid';
        }

        if (strlen($request->request->get('password')) < 6 || strlen($request->request->get('password')) > 16) {
            $this->errors['password'] = 'Password must be from 6 to 16 characters long';
        }

        if ($request->request->get('password') !== $request->request->get('password_confirm')) {
            $this->errors['password_confirm'] = 'Passwords do not match';
        }

        if ($this->hasErrors()) {
            return;
        }

        if ($this->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy(['email' => $request->request->get('email')])) {

            $this->errors['email'] = 'This email is already taken';
        }
    }
}
