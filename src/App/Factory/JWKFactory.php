<?php

namespace JWTDemo\App\Factory;

class JWKFactory
{
    /**
     * @return \Jose\Component\Core\JWK
     */
    public static function create()
    {
        $key = \Jose\Component\KeyManagement\JWKFactory::createFromKeyFile(
            __DIR__ . '/../../../jwk/cert.pem',
            '',
            [
                'use' => 'sig',
            ]
        );

        return $key;
    }
}