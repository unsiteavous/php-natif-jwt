<?php

namespace App\Services;

use DomainException;
use Error;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use UnexpectedValueException;

final class JWTService
{
  private ?string $privateKey = null;
  private ?string $publicKey = null;
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
    $this->privateKey = file_get_contents(__DIR__ . "/../config/keys/private.pem");
    $this->publicKey = file_get_contents(__DIR__ . "/../config/keys/public.pub");

    require __DIR__ . '/../config/config.php';
  }

  public function encode(array $payload = []): string
  {
    $defautPayload = [
      'iss' => URL_BACK, // site qui émet le JWT
      'sub' => '', // sujet du JWT, doit être unique (souvent une adresse email)
      'aud' => URL_FRONT, // site qui va utiliser le JWT
      'exp' => (new \DateTimeImmutable(datetime: 'now', timezone: new \DateTimeZone('Europe/Paris')))->modify('+' . JWT_LIFETIME . ' second')->getTimestamp(), // date d'expiration
      'iat' => (new \DateTimeImmutable(datetime: 'now', timezone: new \DateTimeZone('Europe/Paris')))->getTimestamp(), // date de création
      'nbf' => (new \DateTimeImmutable(datetime: 'now', timezone: new \DateTimeZone('Europe/Paris')))->getTimestamp(), // date de début de validité (pour différer l'utilisation du token à plus tard)
      'jti' => uniqid(more_entropy: true), // identifiant unique du JWT
    ];

    $payload = array_merge($defautPayload, $payload);

    return JWT::encode($payload, $this->privateKey, 'RS256');
  }

  public function decode(string $jwt): object|string
  {
    try {
      return JWT::decode($jwt, new Key($this->publicKey, 'RS256'));
    } catch (DomainException $e) {
      return $e->getMessage();
    } catch (UnexpectedValueException $e) {
      // provided JWT is malformed OR
      // provided JWT is missing an algorithm / using an unsupported algorithm OR
      // provided JWT algorithm does not match provided key OR
      // provided key ID in key/key-array is empty or invalid OR
      // provided JWT is trying to be used after "exp" claim OR
      // provided JWT is trying to be used before "nbf" claim OR
      // provided JWT is trying to be used before "iat" claim OR
      // unknown error thrown in openSSL or libsodium OR
      // libsodium is required but not available.
      return $e->getMessage();
    }
  }
}
