<?php 
require './vendor/autoload.php';

use App\Services\JWTService;

$JWTService = JWTService::getInstance();
$headers = getallheaders();

// On vérifie que le Content-Type soit bien "application/json". Dans le cas où il n'y a pas de Content-Type, on ne fait rien (pour ne pas casser le code avec une requête qui n'envoie pas de body (Options par exemple)).
if (!isset($headers['Content-Type']) && $headers['Content-Type'] !== 'application/json') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Le header "Content-Type" doit avoir la valeur "application/json"']);
    exit;
}

$jwt = $headers['Authorization'] ?? '';

if ($jwt && str_starts_with($jwt, 'Bearer ')) {
    $jwt = substr($jwt, 7); // Retire "Bearer "
}

$decoded = $JWTService->decode($jwt);

header('Content-Type: application/json');

if (is_string($decoded)) {
    echo json_encode(['error' => $decoded]);
    exit;
}

echo json_encode([
    'message' => 'Bonjour User !'
]);