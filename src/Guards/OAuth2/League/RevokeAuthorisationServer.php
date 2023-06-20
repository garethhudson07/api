<?php

namespace Api\Guards\OAuth2\League;

use DateTimeZone;
use League\OAuth2\Server\CryptKey;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\SignedWith;


class RevokeAuthorisationServer
{
    protected $accessTokenRepository;

    protected $refreshTokenRepository;

    protected $publicKey;

    public function __construct($accessTokenRepository, $refreshTokenRepository, $publicKey)
    {
        if ($publicKey instanceof CryptKey === false) {
            $publicKey = new CryptKey($publicKey);
        }

        $this->accessTokenRepository = $accessTokenRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
        $this->publicKey = $publicKey;
    }

    public function respondToRevokeAccessTokenRequest(ServerRequestInterface $request, ResponseInterface $response) {
        $jwtConfiguration = Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('empty', 'empty')
        );

        $jwtConfiguration->setValidationConstraints(
            new LooseValidAt(
                new SystemClock(new DateTimeZone(\date_default_timezone_get())),
                null
            ),
            new SignedWith(
                new Sha256(),
                InMemory::plainText($this->publicKey->getKeyContents(), $this->publicKey->getPassPhrase() ?? '')
            )
        );

        $data = $request->getParsedBody();
        $repositories = [];
        $repositoriesMap = [
            'access_token' => [
                'repository' => $this->accessTokenRepository,
                'method' => 'revokeAccessToken'
            ],
            'refresh_token' => [
                'repository' => $this->refreshTokenRepository,
                'method' => 'revokeRefreshToken'
            ]
        ];

        if (array_key_exists('token_type_hint', $data)) {
            $repositories[] = $data['token_type_hint'];
        } else {
            $repositories[] = 'access_token';
            $repositories[] = 'refresh_token';
        }

        $token = $jwtConfiguration->parser()->parse($data['access_token'] ?? null);
        $id = $token->claims()->get('jti');

        foreach ($repositories as $key) {
            $item = $repositoriesMap[$key];
            $item['repository']->{$item['method']}($id);
        }

        return $response;
    }
}
