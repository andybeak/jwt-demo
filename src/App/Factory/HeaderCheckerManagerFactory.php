<?php

namespace JWTDemo\App\Factory;

use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Signature\JWSTokenSupport;

class HeaderCheckerManagerFactory
{

    /**
     * @return HeaderCheckerManager
     */
    public static function create()
    {
        return new HeaderCheckerManager(
            [
                new AlgorithmChecker(['RS256']),
            ],
            [
                new JWSTokenSupport(),
            ]
        );
    }

}