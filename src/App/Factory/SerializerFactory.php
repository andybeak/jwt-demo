<?php

namespace JWTDemo\App\Factory;

use Jose\Component\Signature\Serializer\CompactSerializer;

class SerializerFactory
{
    /**
     * @return CompactSerializer
     */
    public static function create()
    {
        return new CompactSerializer();
    }
}