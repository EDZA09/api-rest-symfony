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

    // Si existe, Generar el token

    // Comprobar el flag gettoken, condición

    // DPevolver datos
    return "Hola mundo, desde el servicio";
  }
}

