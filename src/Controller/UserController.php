<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;
use App\Entity\Video;

class UserController extends AbstractController
{
    
    public function index(): JsonResponse
    {
        
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $video_repo = $this->getDoctrine()->getRepository(User::class);
        
        $users = $user_repo->findAll();
        
        foreach ($users as $user){
            echo "<h1>{$user->getName()} {$user->getSurname()}</h1>";
        }
        
        die();
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }
}
