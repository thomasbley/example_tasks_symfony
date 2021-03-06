<?php

namespace App\Service;

use App\Model\Customer;
use Exception;

class JwtManager
{
    protected string $publicKey;

    protected string $privateKey;

    public function __construct(string $publicKey, string $privateKey)
    {
        $this->publicKey = $publicKey;
        $this->privateKey = $privateKey;
    }

    public function getCustomer(string $token): ?Customer
    {
        if (substr_count($token, '.') !== 2) {
            return null;
        }

        list($header, $payload, $signature) = explode('.', substr($token, 7));

        $data = $header . '.' . $payload;

        if (openssl_verify($data, $this->urlBase64Decode($signature), $this->publicKey, OPENSSL_ALGO_SHA512) !== 1) {
            return null;
        }

        $payload = json_decode($this->urlBase64Decode($payload), true, 10);

        if (empty($payload['exp']) || $payload['exp'] < time() || empty($payload['sub']) || empty($payload['email'])) {
            return null;
        }

        $customer = new Customer();
        $customer->id = (int) $payload['sub'];
        $customer->email = $payload['email'];

        return $customer;
    }

    public function getToken(Customer $customer): string
    {
        $header = ['typ' => 'JWT', 'alg' => 'RS512'];

        $payload = [
            'exp' => strtotime('+1 day'),
            'sub' => (string) $customer->id,
            'email' => $customer->email,
        ];
        $data = $this->urlBase64Encode(json_encode($header)) . '.' . $this->urlBase64Encode(json_encode($payload));

        $signature = '';
        if (!openssl_sign($data, $signature, $this->privateKey, OPENSSL_ALGO_SHA512)) {
            throw new Exception('signing failed');
        }

        return 'Bearer ' . $data . '.' . $this->urlBase64Encode($signature);
    }

    protected function urlBase64Decode(string $string): string
    {
        return base64_decode(strtr($string, '-_', '+/'));
    }

    protected function urlBase64Encode(string $string): string
    {
        return rtrim(strtr(base64_encode($string), '+/', '-_'), '=');
    }
}
