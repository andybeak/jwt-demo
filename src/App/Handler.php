<?php

namespace JWTDemo\App;

use JWTDemo\App\Exceptions\EncodingException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;


class Handler
{
    /**
     * We will only accept tokens that have this name as their audience
     */
    const MY_APPPLICATION_NAME = 'JWT Demo';

    /**
     * We will only accept tokens that are issued by this server
     */
    const ISSUER = 'My OAuth2 authorization server';

    /**
     * Error message
     */
    const TOKEN_IS_NOT_VALID = 'Token is not valid.';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var JWTwrap
     */
    private $JWTwrap;

    /**
     * @var TokenBlacklist
     */
    private $tokenBlacklist;

    /**
     * @param Request $request
     * @param array $vars
     * @throws EncodingException
     */
    public function create_jwt(Request $request, array $vars): void
    {
        $this->logger->debug(__METHOD__ . ' : bof');

        try {

            // The payload we want to sign. The payload MUST be a string hence we use our JSON Converter.
            $payload = json_encode([
                'iat' => time(),
                'nbf' => time(),
                'exp' => time() + 3600,
                'iss' => self::ISSUER,
                'aud' => self::MY_APPPLICATION_NAME,
            ]);

            $token = $this->JWTwrap->makeJWT($payload);

            echo "<h1>Token</h1><pre>$token</pre></h1>";

            echo '<a href="http://localhost:8000/check/' . $token . '">Click here to check it</a>';

        } catch (\Throwable $e) {

            $this->logger->error(__METHOD__ . ' : ' . $e->getMessage());

            throw new EncodingException('Could not encode JWT');

        }

    }

    /**
     * @param Request $request
     * @param array $vars
     * @throws EncodingException
     */
    public function check_jwt(Request $request, array $vars): void
    {
        $this->logger->debug(__METHOD__ . ' : bof');

        $token = $vars['token'];

        if ($this->tokenBlacklist->isTokenBlacklisted($token)) {

            $this->logger->warning(__METHOD__ . ' : Token is blacklisted');

            throw new EncodingException(self::TOKEN_IS_NOT_VALID);

        }

        try {

            $jws = $this->JWTwrap->loadJWS($token);

            $claims = json_decode($jws->getPayload(), true);

            if ($claims['aud'] !== self::MY_APPPLICATION_NAME) {

                $this->logger->warning(__METHOD__ . ' : Token audience does not match our application');

                throw new EncodingException(self::TOKEN_IS_NOT_VALID);

            }

            if ($claims['iss'] !== self::ISSUER) {

                $this->logger->warning(__METHOD__ . ' : Issuer of token is not who we expect.');

                throw new EncodingException(self::TOKEN_IS_NOT_VALID);

            }

            echo "<h1>Token appears valid</h1>";

            echo '<pre>' . print_r($claims, true) . '</pre>';

            echo '<a href="http://localhost:8000/logout/' . $token . '">Click here to logout</a>';


        } catch (\Throwable $e) {

            $this->logger->error(__METHOD__ . ' : ' . $e->getMessage());

            throw new EncodingException(self::TOKEN_IS_NOT_VALID);

        }

    }

    /**
     * Adds the token to the blacklist so that it will no longer be considered valid
     * JWS are self-signed so there is no way to revoke their validity individually
     *
     * @param Request $request
     * @param array $vars
     */
    public function logout_handler(Request $request, array $vars): void
    {
        $token = $vars['token'];

        $this->tokenBlacklist->addTokenThumbToBlacklist($token);

        echo "Added token to blacklist<br>";

        echo '<a href="http://localhost:8000/check/' . $token . '">Click here to check it</a>';
    }

    /**
     * Setter injection
     * @param LoggerInterface $logger
     * @return $this
     */
    public function setLogger(LoggerInterface $logger): Handler
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @param JWTwrap $JWTwrap
     * @return Handler
     */
    public function setJWTwrap(JWTwrap $JWTwrap): Handler
    {
        $this->JWTwrap = $JWTwrap;

        return $this;
    }

    /**
     * @param TokenBlacklist $tokenBlacklist
     * @return Handler
     */
    public function setTokenBlacklist(TokenBlacklist $tokenBlacklist): Handler
    {
        $this->tokenBlacklist = $tokenBlacklist;

        return $this;
    }
}