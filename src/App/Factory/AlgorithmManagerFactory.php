<?php

namespace JWTDemo\App\Factory;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\RS256;

class AlgorithmManagerFactory
{
    /**
     * @return AlgorithmManager
     */
    public static function create()
    {
        return new AlgorithmManager([
            new RS256()
        ]);
    }
}