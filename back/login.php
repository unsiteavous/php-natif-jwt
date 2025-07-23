<?php
require './vendor/autoload.php';

use App\Services\JWTService;

$JWTService = JWTService::getInstance();

$data = json_decode(file_get_contents('php://input'), true);

if (
  isset($data['username'])
  && isset($data['password'])
  && $data['username'] == 'admin'
  && $data['password'] == 'admin'
) {
  $jwt = $JWTService->encode(['sub' => 'user@example.net']);

  header('Content-Type: application/json');
  echo json_encode(array('token' => $jwt));
} else {
  header('Content-Type: application/json');
  echo json_encode(array('error' => "Nom d'utilisateur ou mot de passe incorrect"));
}
  