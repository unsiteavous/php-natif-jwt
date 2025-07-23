# Installation en back

Sans surprise, on a une petite ligne de commande : 

```bash
composer require firebase/php-jwt
```

Comme indiqué dans la [documentation](https://github.com/firebase/php-jwt), cela nous permet d'utiliser deux méthodes toutes prêtes, `encode` et `decode`.

Puisque c'est quelque chose qu'on va utiliser tout le temps, je vous propose de faire un service, et de le créer avec un design pattern particulier : le [singleton](https://refactoring.guru/fr/design-patterns/singleton). Cela nous permet de nous assurer que nous n'instancieront qu'une et une seule fois ce service, pour éviter de dupliqer la mémoire et les infos.

```php
<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use UnexpectedValueException;

final class JWTService
{
  private static ?JWTService $instance = null;

  public static function getInstance(): JWTService
  {
    if (self::$instance === null) {
      self::$instance = new JWTService();
    }
    return self::$instance;
  }

  private function __construct()
  {
    // ...
  }
  ```

Cela nous permet, en faisant 
```php
use App\Services\JWTService;

$JWTService = JWTService::getInstance();
```

de toujours récupérer la même instance de notre Service.

## Configuration générique
Afin de faire les choses bien, on va également centraliser les informations, pour éviter d'avoir à les modifier à différents endroits :

On va créer une clé privée et une clé publique avec openssl, ainsi qu'un fichier de config dans lequel on pourra mettre par exemple la durée de vie du token: 
```php
define('JWT_LIFETIME', 60);
```

### Pour créer la clé privée : 
```bash
openssl genrsa -out ./config/keys/private.pem 4096
```
Cela crée directement le fichier. Attention, le dossier de destination doit exister avant de lancer la commande.

### Créer la clé publique :
```bash
openssl rsa -in ./config/keys/private.pem -pubout
```
Cela vous donne dans la console la clé, que vous devez copier et coller dans un fichier, auquel vous donnez une extension `.txt`, `.pub`, ...

ensuite, dans le service, on va de quoi récupérer toutes ces infos : 
- les propriétés : 
  ```php
    private ?string $privateKey = null;
    private ?string $publicKey = null;
  ```
- et le constructeur :
  ```php
    $this->privateKey = file_get_contents(__DIR__ . "/../config/keys/private.pem");
    $this->publicKey = file_get_contents(__DIR__ . "/../config/keys/public.pub");

    require __DIR__ . '/../config/config.php';
  ```

## Méthodes `encode` et `decode`
On va ajouter deux méthodes à ce service :

### encode

```php
  public function encode(array $payload = []): string
  {
    $defautPayload = [
      'iss' => 'http://phpjwt/back/', // site qui émet le JWT
      'sub' => '', // sujet du JWT, doit être unique (souvent une adresse email)
      'aud' => 'http://phpjwt/front/', // site qui va utiliser le JWT
      'exp' => (new \DateTimeImmutable(datetime: 'now', timezone: new \DateTimeZone('Europe/Paris')))->modify('+' . JWT_LIFETIME . ' second')->getTimestamp(), // date d'expiration
      'iat' => (new \DateTimeImmutable(datetime: 'now', timezone: new \DateTimeZone('Europe/Paris')))->getTimestamp(), // date de création
      'nbf' => (new \DateTimeImmutable(datetime: 'now', timezone: new \DateTimeZone('Europe/Paris')))->getTimestamp(), // date de début de validité (pour différer l'utilisation du token à plus tard)
      'jti' => uniqid(more_entropy: true), // identifiant unique du JWT
    ];

    $payload = array_merge($defautPayload, $payload);

    return JWT::encode($payload, $this->privateKey, 'RS256');
  }
```

Cette méthode définie un payload par défaut, qu'elle surcharge avec le payload facultatif qu'on peut passer en paramètre. Cela permet de pouvoir créer facilement des payloads sans se poser de questions, mais de laisser la liberté d'aller un peu plus loin dans des cas précis.

### decode

```php
public function decode(string $jwt): object|string
  {
    try {
      return JWT::decode($jwt, new Key($this->publicKey, 'RS256'));
    } catch (UnexpectedValueException $e) {
      // provided JWT is malformed OR
      // provided JWT is missing an algorithm / using an unsupported algorithm OR
      // provided JWT algorithm does not match provided key OR
      // provided key ID in key/key-array is empty or invalid.
      return $e->getMessage();
    }
  }
```

Celle-ci permet de récupérer le payload d'un token qui aurait été passé par le front. Dans le cas où il est expiré, qu'il y a un problème, ... une erreur est levée, qu'on récupère. Dans le cas où tout va bien, on récupère le tableau encodé. Pour plus de détail sur les erreurs, aller lire la doc de php-jwt. 

