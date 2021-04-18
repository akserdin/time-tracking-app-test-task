<?php
/*
 * Created by Dmytro Zolotar. on 17/04/2021.
 * Copyright (c) 2021. All rights reserved.
 */
namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserStoreService
{
    private UserPasswordEncoderInterface $passwordEncoder;
    private EntityManagerInterface $em;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $em)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->em = $em;
    }

    public function storeUser(Request $request): User
    {
        $user = new User();
        $user->setEmail($request->request->get('email'));
        $user->setPassword($this->passwordEncoder->encodePassword($user, $request->request->get('password')));

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
