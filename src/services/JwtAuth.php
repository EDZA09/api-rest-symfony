<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\User;

class JwtAuth {

  public $manager;
  public $key;

  public function __construct($manager){
    $this->manager = $manager;
    $this->key = "esta_es_la_secret.12496285";
  }

  public function signup($email, $password, $gettoken = null){
    // Comprobar si el usuario existe
    $user = $this->manager->getRepository(User::class)->findOneBy([
      'email' => $email,
      'password' => $password
    ]);
    $signup = false;
    // Si existe, Generar el token
    if(is_object($user)){
      $signup = true;
    }
    if($signup){
      $token = [
        'sub' => $user->getId(),
        'name' => $user->getName(),
        'surname' => $user->getSurname(),
        'email' => $user->getEmail(),
        'iat' => time(),
        'exp'=> time() + (7*24*60*60)
      ];
      
      // Comprobar el flag gettoken, condición
      $jwt = JWT::encode($token, $this->key, 'HS256');
      if(!empty($gettoken)){
        $data = $jwt;
      } else {
        $decoded = JWT::decode($jwt, new Key ($this->key,'HS256'));
        $data = $decoded;
      }
    } else {
      $data = [
        'status' => 'error',
        'code' => 400,
        'message' => 'Login Incorrecto'
      ];
    }

    // Devolver datos
    return $data;
  }
}