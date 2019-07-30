<?php

namespace Api\Guards\OAuth2\League;

use Api\Guards\OAuth2\League\Repositories\Client as ClientRepository;
use Api\Guards\OAuth2\League\Repositories\AccessToken as AccessTokenRepository;
use Api\Guards\OAuth2\League\Repositories\RefreshToken as RefreshTokenRepository;
use Api\Guards\OAuth2\League\Repositories\Scope as ScopeRepository;
use Api\Guards\OAuth2\League\Repositories\User as UserRepository;
use Api\Config\Service;
use League\OAuth2\Server\Grant\ClientCredentialsGrant;
use League\OAuth2\Server\Grant\PasswordGrant;
use League\OAuth2\Server\ResourceServer;
use League\OAuth2\Server\AuthorizationServer;
use Defuse\Crypto\Key;
use Stitch\Stitch;
use DateInterval;
use Exception;

class Factory
{
    protected $config;

    /**
     * Factory constructor.
     * @param Service $config
     */
    public function __construct(Service $config)
    {
        $this->config = $config;
    }

    /**
     * @return Service
     */
    public static function config(): Service
    {
        return (new Service())->accepts(
            'publicKeyPath',
            'privateKeyPath',
            'encryptionKey',
            'grants',
            'userRepository'
        );
    }

    /**
     * @return ClientRepository
     */
    public function clientRepository()
    {
        return new ClientRepository(Stitch::make(function ($table)
        {
            $table->name('oauth_clients');
            $table->string('id')->primary();
            $table->string('name');
            $table->string('secret');
        })->hasMany('redirects', Stitch::make(function ($table)
        {
            $table->name('oauth_client_redirects');
            $table->integer('id')->autoIncrement()->primary();
            $table->string('client_id')->references('id')->on('oauth_clients');
            $table->string('uri');
        })));
    }

    /**
     * @return AccessTokenRepository
     */
    public function accessTokenRepository()
    {
        return new AccessTokenRepository(Stitch::make(function ($table)
        {
            $table->name('oauth_access_tokens');
            $table->string('id')->primary();
            $table->string('client_id');
            $table->integer('user_id');
            $table->boolean('revoked');
            $table->datetime('expires_at');
        }));
    }

    /**
     * @return RefreshTokenRepository
     */
    public function refreshTokenRepository()
    {
        return new RefreshTokenRepository(Stitch::make(function ($table)
        {
            $table->name('oauth_refresh_tokens');
            $table->string('id')->primary();
            $table->string('oauth_access_token_id');
            $table->boolean('revoked');
            $table->datetime('expires_at');
        }));
    }

    /**
     * @return ScopeRepository
     */
    public function scopeRepository()
    {
        return new ScopeRepository();
    }

    /**
     * @param $baseRepository
     * @return UserRepository
     */
    public function userRepository($baseRepository)
    {
        return new UserRepository($baseRepository);
    }

    /**
     * @return ResourceServer
     */
    public function resourceServer()
    {
        return new ResourceServer(
            $this->accessTokenRepository(),
            $this->config->get('publicKeyPath')
        );
    }

    /**
     * @return AuthorizationServer
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     */
    public function authorisationServer()
    {
        $server = new AuthorizationServer(
            $this->clientRepository(),
            $this->accessTokenRepository(),
            $this->scopeRepository(),
            $this->config->get('privateKeyPath'),
            Key::loadFromAsciiSafeString($this->config->get('encryptionKey'))
        );

        foreach ($this->config->get('grants') as $name) {
            $server->enableGrantType($this->grant($name), new DateInterval('PT1H'));
        }

        return $server;
    }

    /**
     * @param string $name
     * @return ClientCredentialsGrant|PasswordGrant
     * @throws Exception
     */
    public function grant(string $name)
    {
        switch ($name) {
            case 'client_credentials';
                return $this->clientCredentialsGrant();

            case 'password';
                return $this->passwordGrant();
        }

        throw new Exception('Unsupported grant type');
    }

    /**
     * @return ClientCredentialsGrant
     */
    public function clientCredentialsGrant()
    {
        return new ClientCredentialsGrant();
    }

    /**
     * @return PasswordGrant
     * @throws Exception
     */
    public function passwordGrant()
    {
        $grant = new PasswordGrant(
            $this->userRepository($this->config->get('userRepository')),
            $this->refreshTokenRepository()
        );

        $grant->setRefreshTokenTTL(new DateInterval('P1M'));

        return $grant;
    }
}
