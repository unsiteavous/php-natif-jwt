# Utilisation

## Création
Pour créer un token avec notre service, rien de plus simple : 

```php
<?php
require './vendor/autoload.php';

use App\Services\JWTService;

$JWTService = JWTService::getInstance();

header('Content-Type: application/json');
echo json_encode(array('token' => $jwt));
```

## Vérification
pour le récupérer et le lire, on va avoir quelques étapes. Tout d'abord, le front doit garder ce token. Le plus simple est de le stocker en localstorage. Ce n'est pas le plus sécurisé, mais dans un premier temps, ce sera super.

Ensuite à chaque requête, le front doit ajouter une ligne au header du fetch, pour ajouter le token : 

```js
fetch('http://phpjwt/back/', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + localStorage.getItem('token')
    },
    // body: JSON.stringify({ data: "ce qu'on veut" })
  }).then(response => {
    //...
  }
```

Cette ligne d'Authorization permet d'envoyer le token au back, pour qu'il soit lu avant même de travailler sur le contenu de la requête. Même s'il est possible de faire autrement (par exemple en passant le token directement dans le body), faites comme cela, car toutes les API REST que vous aurez à utiliser ou à créer travailleront ainsi, avec `Bearer`.

Côté back, il faut maintenant récupérer le token, l'analyser et répondre.

```php
<?php 
require './vendor/autoload.php';

use App\Services\JWTService;

$JWTService = JWTService::getInstance();
$headers = getallheaders(); // permet de récupérer tous les headers envoyés.

$jwt = $headers['Authorization'] ?? ''; // on récupère celui qui nous intéresse.

if ($jwt && str_starts_with($jwt, 'Bearer ')) {
    $jwt = substr($jwt, 7); // Retire "Bearer "
}

$decoded = $JWTService->decode($jwt); // cela nous répond un tableau si tout va bien, une string (avec le message de l'erreur) si quelque chose ne va pas.

header('Content-Type: application/json');

if (is_string($decoded)) {
    echo json_encode(['error' => $decoded]); // on renvoie l'erreur
    exit;
}

echo json_encode([
    'message' => 'Bonjour User !' 
]); // on renvoie ce qu'on veut ! 
```

et voilà, on a fait le tour ! 

Le reste est du cosmétique, que vous retrouverez dans le code. 