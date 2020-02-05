<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\PasswordResetRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\PasswordResetRequestTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PasswordResetRequestRepository")
 */
class PasswordResetRequest implements PasswordResetRequestInterface
{
    //@TODO ugly
    use PasswordResetRequestTrait {
        PasswordResetRequestTrait::__construct as private __traitConstruct;
    }

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $user;

    public function __construct(UserInterface $user, \DateTimeImmutable $expiresAt, string $selector, string $hashedToken)
    {
        $this->user = $user;

        //@TODO ugly
        $this->__traitConstruct($expiresAt, $selector, $hashedToken);
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }
}
