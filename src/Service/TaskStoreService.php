<?php
/*
 * Created by Dmytro Zolotar. on 17/04/2021.
 * Copyright (c) 2021. All rights reserved.
 */
namespace App\Service;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

class TaskStoreService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function storeUserTask($user, array $data)
    {
        $task = new Task();

        $task->setTitle($data['title'])
            ->setComment($data['comment'])
            ->setTimeSpent($data['timeSpent'])
            ->setCreatedAt($data['createdAt'])
            ->setUser($user);

        $this->em->persist($task);
        $this->em->flush();
    }
}
