<?php

namespace App\Services;

use Firebase\JWT\JWT;
use App\Entity\User;

class JwtAuth {

  public $manager;

  public function __construct($manager){
    $this->manager = $manager;
  }

  public function signup($email, $password){
    // Comprobar si el usuario existe
    $user = $this->manager->geRepositoty(User::class)->findOneBy([
      'email' => $email,
      'password' => $password
    ]);

    // Comprobar el flag gettoken, condición

    // Devolver datos
    return "Hola mundo, desde el servicio";
  }
}