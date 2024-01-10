<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\User;
use App\Entity\Video;

class UserController extends AbstractController
{
 
    public function resjson(){
      // Serializar datos con servicio serializer

      // Response con httpFoundation

      // Asignar contenido a la respuesta

      // Indicar formato de respuesta

      // Devolver la respuesta
    }
    public function index(): JsonResponse
    {
        
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $video_repo = $this->getDoctrine()->getRepository(User::class);
        
        $users = $user_repo->findAll();

        $user = $user_repo->find(1);

        $data = [
          'message' => 'Welcome to your new controller!',
          'path' => 'src/Controller/UserController.php',
        ]
        /*
        foreach ($users as $user){
            echo "<h1>{$user->getName()} {$user->getSurname()}</h1>";
            
            foreach($user->getVideos() as $video){
                echo "<p>{$video->getTitle()} - {$video->getUser()->getEmail()}</p>";
            }
        }
        
        die();*/
        return $this->json($user);
    }
}
