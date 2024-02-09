<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\Email;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;

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

    public function newVideo(Request $request, JwtAuth $jwt_auth, ManagerRegistry $doctrine)
    {
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El vídeo no ha podido crearse'
        ];

        // Recoger el token
        $token = $request->headers->get('Authorization');

        // Comprobar si es correcto
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
            // Recoger datos por post
            $json = $request->get('json', null);
            $params = json_decode($json);

            // Recoger el objeto del usuario identificado
            $identity = $jwt_auth->checkToken($token, true);

            // Comprobar y Validar datos
            if (! empty($json)) {
                $user_id = ($identity->sub != null) ? $identity->sub : null;
                $title = (! empty($params->title)) ? $params->title : null;
                $description = (! empty($params->description)) ? $params->description : null;
                $url = (! empty($params->url)) ? $params->url : null;

                if (! empty($user_id) && ! empty($title)) {
                    // Guardar el nuevo video favorito en la bd
                    $em = $doctrine->getManager();
                    $user = $doctrine->getRepository(User::class)->findOneBy([
                        'id' => $user_id
                    ]);

                    $video = new Video();
                    $video->setUser($user);
                    $video->setTitle($title);
                    $video->setDescription($description);
                    $video->setUrl($url);
                    $video->setStatus('normal');

                    $createdAt = new \DateTime('now');
                    $updatedAt = new \DateTime('now');

                    $video->setCreatedAt($createdAt);
                    $video->setUpdatedAt($updatedAt);

                    // Guardar en la base de datos
                    $em->persist($video);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El video se ha guardado',
                        'video' => $video
                    ];
                }
            }
        }

        // Devolver una respuesta

        return $this->resjson($data);
    }

    public function videos(Request $request, JwtAuth $jwt_auth, PaginatorInterface $paginator, EntityManagerInterface $entity)
    {
        // Obtener el Header de autorización
        $token = $request->headers->get('Authorization');

        // Comprobar el token
        $authCheck = $jwt_auth->checkToken($token);

        // Si es valido,
        if ($authCheck) {
            // Conseguir la identidad del usuario
            $identity = $jwt_auth->checkToken($token, true);

            // Configurar el bundle de paginación -> en services.yaml y bundles.php
            
            // Hacer una consulta para paginar
            $dql = "SELECT v FROM App\Entity\Video v Where v.user = {$identity->sub} ORDER BY v.id DESC";
            $query = $entity->createQuery($dql);

            // Recoger el parámetro de la url
            $page = $request->query->getInt('page', 1);
            $items_per_page = 5;

            // Invocar paginación
            $pagination = $paginator->paginate($query,$page,$items_per_page);
            $total = $pagination->getTotalItemCount();

            // Preparar array de datos a retornar
            $data = [
                'status' => "success",
                'code' => 200,
                'total_items_count' => $total,
                'page_actual' => $page,
                'items_per_page' => $items_per_page,
                'total_pages' => ceil($total/ $items_per_page),
                'videos' => $pagination,
                'user_id' => $identity->sub
            ];
        } else {
            $data = [
                'status' => "error",
                'code' => 400,
                'message' => "NO se pueden listar los videos en este momento"
            ];
        }
        return $this->resjson($data);
    }
    
    public function video(Request $request, JwtAuth $jwt_auth, $id = null): JsonResponse {
        $data = [
            'status' => "error",
            'code' => 400,
            'message' => "Video no entrado",
            'id' => $id
        ];
        return new JsonResponse($data);
    }
}
