<?php

/**
 * Using a file is convenient for demonstration, but far from ideal for production because:
 *
 *      1) It is likely that you will encounter locks and problems with race conditions
 *
 *      2) It requires disk I/O that we can't manage efficiently
 *
 * In any real situation you'd want to use a database, a cache like Memcached, or a NoSQL store like Redis
 */

namespace JWTDemo\App;

class TokenBlacklist
{
    /**
     * Where to store the tokens
     */
    const STORAGE_FILE = '../../token_blacklist.json';

    /**
     * This must be longer than the token validity time
     */
    const BLACKLIST_VALIDITY_SECONDS = 86400;

    /**
     * An array in the form:
     * [
     *      [ 'token_thumb' => '123', 'timestamp' => 123 ]
     * ]
     * @var array
     */
    private $blacklistedTokens = [];


    /**
     * Loads the tokens from the file
     */
    public function loadFromFile(): void
    {
        $fileContents = file_get_contents(self::STORAGE_FILE);

        $this->blacklistedTokens = json_decode($fileContents, true);
    }

    /**
     * Loop through the tokens and remove any of them that are too old,
     * replace the existing file with the trimmed down list
     */
    public function expireOldTokens(): void
    {
        $wereAnyTokensRemoved = false;

        $validTokens = [];

        foreach ($this->blacklistedTokens as $token) {

            if (time() < $token['timestamp'] + self::BLACKLIST_VALIDITY_SECONDS) {

                $validTokens[] = $token;

            } else {

                $wereAnyTokensRemoved = true;

            }
        }

        $this->blacklistedTokens = $validTokens;

        if (true === $wereAnyTokensRemoved) {

            $this->writeTokensToFile();

        }
    }

    /**
     * Write a json encoded string of the current tokens to disk
     */
    private function writeTokensToFile()
    {
        file_put_contents(self::STORAGE_FILE, json_encode($this->blacklistedTokens));
    }

    /**
     * @param string $token
     */
    public function addTokenThumbToBlacklist(string $token): void
    {
        $tokenThumb = md5($token);

        if (!$this->isTokenBlacklisted($tokenThumb)) {

            $this->blacklistedTokens[] = [
                'token_thumb' => $tokenThumb,
                'timestamp' => time()
            ];

        }

        $this->writeTokensToFile();

    }

    /**
     * @param string $token
     * @return bool
     */
    public function isTokenBlacklisted(string $token): bool
    {
        $tokenThumb = md5($token);

        foreach ($this->blacklistedTokens as $token) {

            if ($tokenThumb === $token['token_thumb']) {

                return true;

            }

        }

        return false;
    }
}