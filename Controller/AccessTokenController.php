<?php

namespace Dontdrinkandroot\RestBundle\Controller;

use Dontdrinkandroot\RestBundle\Model\UserCredentials;
use Dontdrinkandroot\RestBundle\Service\AccessTokenServiceInterface;
use Dontdrinkandroot\RestBundle\Service\Normalizer;
use Dontdrinkandroot\RestBundle\Service\RestRequestParser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class AccessTokenController
{
    /**
     * @var AccessTokenServiceInterface
     */
    private $accessTokenService;

    /**
     * @var RestRequestParser
     */
    private $restRequestParser;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    public function __construct(
        AccessTokenServiceInterface $accessTokenService,
        RestRequestParser $restRequestParser,
        Normalizer $normalizer,
        TokenStorageInterface $tokenStorage
    ) {
        $this->accessTokenService = $accessTokenService;
        $this->restRequestParser = $restRequestParser;
        $this->normalizer = $normalizer;
        $this->tokenStorage = $tokenStorage;
    }

    public function createAction(Request $request)
    {
        $userCredentials = $this->restRequestParser->parseEntity($request, UserCredentials::class);
        $accessToken = $this->accessTokenService->createAccessToken(
            $userCredentials->getUsername(),
            $userCredentials->getPassword()
        );
        $content = $this->normalizer->normalize($accessToken);

        return new JsonResponse($content, Response::HTTP_CREATED);
    }

    public function listAction(Request $request)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!is_object($user)) {
            throw new AccessDeniedException();
        }

        $tokens = $this->accessTokenService->listByUser($user);
        $content = $this->normalizer->normalize($tokens);

        return new JsonResponse($content);
    }

    public function deleteAction(Request $request, $token)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if (!is_object($user)) {
            throw new AccessDeniedException();
        }

        $accessToken = $this->accessTokenService->findByToken($token);
        if (null === $accessToken) {
            throw new NotFoundHttpException();
        }

        $this->accessTokenService->remove($accessToken);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
