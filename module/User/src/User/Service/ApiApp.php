<?php

namespace User\Service;

use DateTime;
use Firebase\JWT\JWT;
use User\Mapper\ApiApp as ApiAppMapper;
use User\Model\User as UserModel;

class ApiApp
{
    /**
     * @var ApiAppMapper
     */
    protected $mapper;

    /**
     * Constructor.
     */
    public function __construct(ApiAppMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * Get a callback from an appId and a user identity.
     *
     * @param string $appId
     *
     * @return string
     */
    public function callbackWithToken($appId, UserModel $user)
    {
        $app = $this->getMapper()->findByAppId($appId);

        $token = [
            'iss' => 'https://gewis.nl/',
            'lidnr' => $user->getLidnr(),
            'exp' => (new DateTime('+5 min'))->getTimestamp(),
            'iat' => (new DateTime())->getTimestamp(),
            'nonce' => bin2hex(openssl_random_pseudo_bytes(16)),
        ];

        return $app->getCallback().'?token='.JWT::encode($token, $app->getSecret(), 'HS256');
    }

    /**
     * @return ApiAppMapper
     */
    public function getMapper()
    {
        return $this->mapper;
    }
}
