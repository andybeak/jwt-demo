<?php

namespace JWTDemo\App\Factory;

use Jose\Component\Signature\Serializer\JWSSerializerManager;

class SerializerManagerFactory
{
    /**
     * @return JWSSerializerManager
     */
    public static function create()
    {
        return new JWSSerializerManager([
            SerializerFactory::create()
        ]);
    }
}