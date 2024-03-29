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

class UserController extends AbstractController
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
        $user_repo = $this->getDoctrine()->getRepository(User::class);
        $video_repo = $this->getDoctrine()->getRepository(Video::class);

        $users = $user_repo->findAll();
        $user = $user_repo->find(1);

        $videos = $video_repo->findAll();

        $data = [
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php'
        ];
        /*
         * foreach ($users as $user){
         * echo "<h1>{$user->getName()} {$user->getSurname()}</h1>";
         *
         * foreach($user->getVideos() as $video){
         * echo "<p>{$video->getTitle()} - {$video->getUser()->getEmail()}</p>";
         * }
         * }
         *
         * die();
         */
        return $this->json($videos);
    }

    public function create(Request $request)
    {
        // Recoger los datos por post
        $json = $request->get('json', null);

        // Decodificar el json
        // indicar true para convertirlos en un arreglos con valores asociados
        $params = json_decode($json);

        // Respuesta por defecto.
        $data = [
            'status' => 'success',
            'code' => 200,
            'message' => 'El usuario no se ha creado',
            'params' => $params
        ];

        // Comprobar y validar datos
        if ($json != null) {
            $name = (! empty($params->name)) ? $params->name : null;
            $surname = (! empty($params->surname)) ? $params->surname : null;
            $email = (! empty($params->email)) ? $params->email : null;
            $password = (! empty($params->password)) ? $params->password : null;

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if (! empty($email) && count($validate_email) == 0 && ! empty($password) && ! empty($name) && ! empty($surname)) {
                // Si la validación es correcta, crear el objeto del usuario
                $user = new User();
                $user->setName($name);
                $user->setSurname($surname);
                $user->setEmail($email);
                $user->setRole('ROLE_USER');
                $user->setCreatedAt(new \DateTime("now"));

                // Cifrar la contraseña
                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);

                // Comprobar si el usuario existe
                $doctrine = $this->getDoctrine();
                $em = $doctrine->getManager();

                $user_repo = $doctrine->getRepository(User::class);
                $isset_user = $user_repo->findBy(array(
                    'email' => $email
                ));
                if (count($isset_user) == 0) {
                    // si no existe, guardarlo en la bd
                    $em->persist($user);
                    $em->flush();

                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'El usuario Creado Existosamente',
                        'user' => $user
                    ];
                } else {
                    $data = [
                        'status' => 'Error',
                        'code' => 500,
                        'message' => 'El usuario Ya existe'
                    ];
                }
            }
        }

        // respuesta en json
        // ? Primera forma de retornar en JSON
        /*
         * Solo Se debe usar para retornar arreglos o un conjunto de objetos.
         * return $this->resjson($data);
         */
        // ? Segunda forma de retornar en JSON
        /*
         * Se puede usar para retornar objetos o un conjunto de ellos pero aveces
         * presenta problemas para los arreglos.
         * return $this->json($data);
         */
        // ** Tercera forma, esta es la más completa, pero se requiere importar un paquete
        // ** ese es: Symfony\Component\HttpFoundation\JsonResponse;
        return new JsonResponse($data);
    }

    public function login(Request $request, JwtAuth $jwt_auth)
    {
        // Recibir los datos por post
        $json = $request->get('json', null);
        $params = json_decode($json);

        // Mensaje por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'El usuario no se ha podido identificar'
        ];

        // Comprobar y validar los datos
        if ($json != null) {
            $email = (! empty($params->email) ? $params->email : null);
            $password = (! empty($params->password) ? $params->password : null);
            $gettoken = (! empty($params->gettoken) ? $params->gettoken : null);

            $validator = Validation::createValidator();
            $validate_email = $validator->validate($email, [
                new Email()
            ]);

            if (! empty($email) && count($validate_email) == 0 && ! empty($password)) {
                // cifrar contraseña
                $pwd = hash("sha256", $password);

                // Si todo es válido, llamaremos a un servicio
                // para identificar al usuario (token JWT o un objeto JSON)
                if ($gettoken != 'false' || $gettoken != false) {
                    $signup = $jwt_auth->signup($email, $pwd, $gettoken);
                } else {
                    $signup = $jwt_auth->signup($email, $pwd);
                }

                return new JsonResponse($signup);
            }
        }
        // Si nos devuelve bien los datos, lo Retorno.
        return $this->resjson($data);
    }

    public function edit(Request $request, JwtAuth $jwt_auth)
    {
        // Recoger la cabecera de autenticación
        $token = $request->headers->get('Authorization');

        // Crear un método para comprobar si el token es correcto
        $authCheck = $jwt_auth->checkToken($token);

        // Respuesta por defecto
        $data = [
            'status' => 'error',
            'code' => 400,
            'message' => 'Usuario NO Actualizado'
        ];

        // Si es correcto, hacer la actualización del usuario
        if ($authCheck) {
            // Actualizar usuario

            // Conseguir el Entity manager
            $em = $this->getDoctrine()->getManager();

            // Obtener los datos del usuario identificado
            $identity = $jwt_auth->checkToken($token, true);

            // Conseguir el usuario a actualizar completo
            $user_repo = $this->getDoctrine()->getRepository(User::class);
            $user = $user_repo->findOneBy([
                'id' => $identity->sub
            ]);

            // Recoger los datos por POST
            $json = $request->get('json', null);
            $params = json_decode($json);

            // Comprobar y Validar los datos
            if (! empty($json)) {
                $name = (! empty($params->name)) ? $params->name : null;
                $surname = (! empty($params->surname)) ? $params->surname : null;
                $email = (! empty($params->email)) ? $params->email : null;
                
                $validator = Validation::createValidator();
                $validate_email = $validator->validate($email, [
                    new Email()
                ]);

                if (! empty($email) && count($validate_email) == 0 && ! empty($name) && ! empty($surname)) {
                    // Asignar nuevos datos al objeto del usuario
                    $user->setName($name);
                    $user->setSurname($surname);
                    $user->setEmail($email);

                    // Comprobar duplicados
                    $isset_user = $user_repo->findBy([
                        'email' => $email
                    ]);

                    if (count($isset_user) == 0 || strcasecmp($identity->email, $email) == 0) {
                        // Guardar cambios en la base de datos
                        $em->persist($user);
                        $em->flush();

                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'Usuario Actualizado',
                            'user' => $user
                        ];
                    } else {
                        $data = [
                            'status' => 'error',
                            'code' => 400,
                            'message' => 'El Usuario ya existe'
                        ];
                    }
                }
            }
        }

        return $this->resjson($data);
    }
}
