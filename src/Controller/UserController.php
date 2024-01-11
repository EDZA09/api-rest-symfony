<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;

use App\Entity\User;
use App\Entity\Video;

class UserController extends AbstractController
{
    private function resjson($data){
      // Serializar datos con servicio serializer
      $json = $this->get('serializer')->serialize($data, 'json');

      // Response con httpFoundation
      $response = new Response();

      // Asignar contenido a la respuesta
      $response ->setContent($json);

      // Indicar formato de respuesta
      $response->headers->set('Content-Type', 'application/json');

      // Devolver la respuesta
      return $response;

    }

    public function index(): JsonResponse
    {
        
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $video_repo = $this->getDoctrine()->getRepository(Video::class);
        
        $users = $user_repo->findAll();
        $user = $user_repo->find(1);

        $videos = $video_repo->findAll();

        $data = [
          'message' => 'Welcome to your new controller!',
          'path' => 'src/Controller/UserController.php',
        ];
        /*
        foreach ($users as $user){
            echo "<h1>{$user->getName()} {$user->getSurname()}</h1>";
            
            foreach($user->getVideos() as $video){
                echo "<p>{$video->getTitle()} - {$video->getUser()->getEmail()}</p>";
            }
        }
        
        die();*/
        return $this->json($videos);
    }

    public function create(Request $request){
      // Recoger los datos por post
      $json = $request->get('json', null);

      // Decodificar el json
      // indicar true para convertirlos en un arreglos con valores asociados
      $params = json_decode($json);

      // Respuesta por defecto.
      $data = [
        'status' => 'Success',
        'code' => 200,
        'message' => 'El usuario no se ha creado',
        'params' => $params
      ];

      // Comprobar y validar datos
      if($json != null){
        $name = (!empty($params->name)) ? $params->name : null;
        $surname = (!empty($params->surname)) ? $params->surname : null;
        $email = (!empty($params->email)) ? $params->email : null;
        $password = (!empty($params->password)) ? $params->password : null;

        $validator = Validation::createValidator();
        $validate_email = $validator->validate($email, [new Email()]);
      }

      // Si la validación es correcta, crear el objeto del usuario

      // Cifrar la contraseña

      // Comprobar si el usuario existe

      // si no existe, guardarlo en la bd

      // respuesta en json
      //? Primera forma de retornar en JSON
      /*
      Solo Se debe usar para retornar arreglos o un conjunto de objetos.
      return $this->resjson($data);
      */
      //? Segunda forma de retornar en JSON
      /*
      Se puede usar para retornar objetos o un conjunto de ellos pero aveces
      presenta problemas para los arreglos.
      return $this->json($data);
      */
      //** Tercera forma, esta es la más completa, pero se requiere importar un paquete
      //** ese es: Symfony\Component\HttpFoundation\JsonResponse;
      return new JsonResponse($data);
    }
}
