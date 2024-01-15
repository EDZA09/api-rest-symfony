<?php

namespace App\Services;

use Firebase\JWT\JWT;
use App\Entity\User;

class JwtAuth {

  public $manager;
  public $key;

  public function __construct($manager){
    $this->manager = $manager;
    $this->key = "esta_es_la_secret.12496285";
  }

  public function signup($email, $password, $gettoken){
    // Comprobar si el usuario existe
    $user = $this->manager->geRepositoty(User::class)->findOneBy([
      'email' => $email,
      'password' => $password
    ]);
    $signup = false;
    // Si existe, Generar el token
    if(is_object($user)){
      $signup = true;
    }
    // Comprobar el flag gettoken, condición
    if($signup){
      $token = [
        'sub' => $user->id,
        'name' => $user->name,
        'surname' => $user->surname,
        'email' => $user->email,
        'iat' => time(),
        'exp'=> time() + (7*24*60*60)
      ];

      $jwt = JWT::encode($token, $this->key, 'HS256');
    }

    // Devolver datos
    return "Hola mundo, desde el servicio";
  }
}