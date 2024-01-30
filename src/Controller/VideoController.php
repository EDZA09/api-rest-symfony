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
use App\Services\JwtAuth;

class VideoController extends AbstractController
{

    private function resjson($data)
    {
        // Serializar datos con servicio serializer
        $json = $this->get('serializer')->serialize($data, 'json');

        // Response con httpFoundation
        $response = new Response();

        // Asignar contenido a la respuesta
        $response->setContent($json);

        // Indicar formato de respuesta
        $response->headers->set('Content-Type', 'application/json');

        // Devolver la respuesta
        return $response;
    }

    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/VideoController.php'
        ]);
    }

    public function newVideo(Request $request, JwtAuth $jwt_auth)
    {
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El vídeo no ha podido crearse'
        ];

        // Recoger el token
        $token = $request->headers->get('Authorization');

        // Comprobar si es correcto
        $authCheck = $jwt_auth->checkToken($jwt);



        // Devolver una respuesta

        return $this->resjson($data);
    }
}
