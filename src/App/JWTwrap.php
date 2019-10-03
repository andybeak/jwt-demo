<?php

namespace JWTDemo\App;

use Jose\Component\Core\JWK;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSVerifier;
use JWTDemo\App\Exceptions\EncodingException;
use JWTDemo\App\Factory\AlgorithmManagerFactory;
use JWTDemo\App\Factory\HeaderCheckerManagerFactory;
use JWTDemo\App\Factory\JWKFactory;
use JWTDemo\App\Factory\SerializerFactory;
use JWTDemo\App\Factory\SerializerManagerFactory;
use Psr\Log\LoggerInterface;

class JWTwrap
{

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $payload
     * @return string
     */
    public function makeJWT(string $payload)
    {
        $jwk = JWKFactory::create();

        $jws = $this->buildJWS($payload, $jwk);

        return $this->serialize($jws);
    }

    /**
     * @param string $payload
     * @param JWK $jwk
     * @return JWS
     */
    public function buildJWS(string $payload, JWK $jwk): JWS
    {
        // The algorithm manager with the RS256 algorithm.
        $algorithmManager = AlgorithmManagerFactory::create();

        // We instantiate our JWS Builder.
        $jwsBuilder = new JWSBuilder($algorithmManager);

        $jws = $jwsBuilder
            ->create()
            ->withPayload($payload)
            ->addSignature($jwk, ['alg' => 'RS256'])
            ->build();

        return $jws;
    }

    /**
     * @param JWS $jws
     * @return string
     */
    public function serialize(JWS $jws)
    {
        $serializer = SerializerFactory::create();

        return $serializer->serialize($jws, 0);
    }

    /**
     * @param string $token
     * @return JWS
     * @throws \Exception
     */
    public function loadJWS(string $token): JWS
    {
        $headerCheckerManager = HeaderCheckerManagerFactory::create();

        $algorithmManager = AlgorithmManagerFactory::create();

        $serializerManager = SerializerManagerFactory::create();

        $jwsVerifier = new JWSVerifier($algorithmManager);

        $jwsLoader = new JWSLoader(
            $serializerManager,
            $jwsVerifier,
            $headerCheckerManager
        );

        $jwk = JWKFactory::create();

        $signature = null;

        try {

            $jws = $jwsLoader->loadAndVerifyWithKey($token, $jwk, $signature);

            return $jws;

        } catch (\InvalidArgumentException $e) {

            $this->logger->error(__METHOD__ . ' : ' . $e->getMessage());

            throw new EncodingException('Token is not valid');

        }

    }

    /**
     * Setter injection
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger): JWTwrap
    {
        $this->logger = $logger;

        return $this;
    }
}