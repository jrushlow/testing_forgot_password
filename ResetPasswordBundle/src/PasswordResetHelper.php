<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymfonyCasts\Bundle\ResetPassword;

use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\InvalidResetPasswordTokenException;
use SymfonyCasts\Bundle\ResetPassword\Exception\TooManyPasswordRequestsException;
use SymfonyCasts\Bundle\ResetPassword\Generator\TokenGenerator;
use SymfonyCasts\Bundle\ResetPassword\Model\PasswordResetRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\PasswordResetToken;
use SymfonyCasts\Bundle\ResetPassword\Persistence\PasswordResetRequestRepositoryInterface;

/**
 * @author Jesse Rushlow <jr@rushlow.dev>
 * @author Ryan Weaver <weaverryan@gmail.com>
 */
class PasswordResetHelper implements PasswordResetHelperInterface
{
    /**
     * Length of time a reset request is valid for
     */
    public const LIFETIME_HOURS = 1;

    /**
     * The first 20 characters of the token are a "selector"
     */
    private const SELECTOR_LENGTH = 20;

    private $repository;

    private $tokenSigningKey;

    /**
     * @var int How long a token is valid in seconds
     */
    private $resetRequestLifetime;

    /**
     * @var int Another password reset cannot be made faster than this throttle time.
     */
    private $requestThrottleTime;

    private $tokenGenerator;

    public function __construct(PasswordResetRequestRepositoryInterface $repository, string $tokenSigningKey, int $resetRequestLifetime, int $requestThrottleTime, TokenGenerator $generator)
    {
        $this->repository = $repository;
        $this->tokenSigningKey = $tokenSigningKey;
        $this->resetRequestLifetime = $resetRequestLifetime;
        $this->requestThrottleTime = $requestThrottleTime;
        $this->tokenGenerator = $generator;
    }

    /**
     * Creates a PasswordResetToken object
     *
     * Some of the cryptographic strategies were taken from
     * https://paragonie.com/blog/2017/02/split-tokens-token-based-authentication-protocols-without-side-channels
     */
    public function generateResetToken(UserInterface $user): PasswordResetToken
    {
        if ($this->hasUserHisThrottling($user)) {
            throw new TooManyPasswordRequestsException();
        }

        $expiresAt = $this->getNewExpiresAt();
        $selector = $this->tokenGenerator->getRandomAlphaNumStr(self::SELECTOR_LENGTH);
        $plainVerifierToken = $this->tokenGenerator->getRandomAlphaNumStr(self::SELECTOR_LENGTH);

        $hashedToken = $this->tokenGenerator->getToken(
            $this->tokenSigningKey,
            $expiresAt,
            $plainVerifierToken,
            $user->getId()
        );

        $passwordResetRequest = $this->repository->createPasswordResetRequest(
            $user,
            $expiresAt,
            $selector,
            $hashedToken
        );

        $this->repository->persistPasswordResetRequest($passwordResetRequest);

        return new PasswordResetToken(
            // final "public" token is the selector + non-hashed verifier token
            $selector.$plainVerifierToken,
            $expiresAt
        );
    }

    public function validateTokenAndFetchUser(string $fullToken): UserInterface
    {
        /** @var PasswordResetRequestInterface $resetToken */
        $resetRequest = $this->findToken($fullToken);

        if ($resetRequest->isExpired()) {
            throw new ExpiredResetPasswordTokenException();
        }

        /** @var UserInterface $user */
        $user = $resetRequest->getUser();

        $verifierToken = substr($fullToken, self::SELECTOR_LENGTH);

        $hashedVerifierToken = $this->tokenGenerator->getToken(
            $this->tokenSigningKey,
            $resetRequest->getExpiresAt(),
            $verifierToken,
            $user->getId()
        );

        if (false === hash_equals($resetRequest->getHashedToken(), $hashedVerifierToken)) {
            throw new InvalidResetPasswordTokenException();
        }

        return $user;
    }

    public function removeResetRequest(string $fullToken): void
    {
        if (empty($fullToken)) {
            throw new InvalidResetPasswordTokenException();
        }

        $request = $this->findToken($fullToken);
        $this->repository->removeResetRequest($request);
    }

    private function findToken(string $token): PasswordResetRequestInterface
    {
        $selector = substr($token, 0, self::SELECTOR_LENGTH);

        return $this->repository->findPasswordResetRequest($selector);
    }

    private function hasUserHisThrottling(UserInterface $user): bool
    {
        $lastRequestDate = $this->repository->getMostRecentNonExpiredRequestDate($user);

        if (!$lastRequestDate) {
            return false;
        }

        if (($lastRequestDate->getTimestamp() + $this->requestThrottleTime) > time()) {
            return true;
        }

        return false;
    }

    private function getNewExpiresAt(): \DateTimeImmutable
    {
        return (new \DateTimeImmutable('now'))
            ->modify(sprintf('+%d seconds', $this->resetRequestLifetime));
    }
}
