<?php

namespace SunAppModules\Core\Http\Controllers\Auth;

use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;
use Psr\Http\Message\ServerRequestInterface;
use SunApp\Http\Controllers\Controller as BaseController;
use SunAppModules\Core\Http\Controllers\Auth\LoginController;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response as Psr7Response;
use Zend\Diactoros\ServerRequest;

class TokenController extends LoginController
{
    /**
     * The authorization server instance.
     *
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    protected $server;

    /**
     * The client repository instance.
     *
     * @var \Laravel\Passport\ClientRepository
     */
    protected $clients;

    /**
     * The token repository instance.
     *
     * @var \Laravel\Passport\TokenRepository
     */
    protected $tokens;

    /**
     * The JWT token parser instance.
     *
     * @var \Lcobucci\JWT\Parser
     */
    protected $jwt;

    /**
     * Create a new personal access token factory instance.
     *
     * @param \League\OAuth2\Server\AuthorizationServer $server
     * @param \Laravel\Passport\ClientRepository $clients
     * @param \Laravel\Passport\TokenRepository $tokens
     * @param \Lcobucci\JWT\Parser $jwt
     * @return void
     */
    public function __construct(AuthorizationServer $server,
                                TokenRepository $tokens,
                                JwtParser $jwt)
    {
        $this->jwt = $jwt;
        $this->server = $server;
        $this->tokens = $tokens;
    }

    /**
     * Create a new personal access token.
     *
     * @param mixed $userId
     * @param string $name
     * @param array $scopes
     * @return \Laravel\Passport\PersonalAccessTokenResult
     */
    public function issueToken(ServerRequestInterface $request)
    {
        return $this->withErrorHandling(function () use ($request) {
            return $this->convertResponse(
                $this->server->respondToAccessTokenRequest($request, new Psr7Response)
            );
        });
    }
}
