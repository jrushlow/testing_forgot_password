<?php

namespace App\Repository;

use App\Entity\PasswordResetRequest;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Model\PasswordResetRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\PasswordResetRequestRepositoryInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\Repository\PasswordResetRequestRepositoryTrait;

/**
 * @method PasswordResetRequest|null find($id, $lockMode = null, $lockVersion = null)
 * @method PasswordResetRequest|null findOneBy(array $criteria, array $orderBy = null)
 * @method PasswordResetRequest[]    findAll()
 * @method PasswordResetRequest[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PasswordResetRequestRepository extends ServiceEntityRepository implements PasswordResetRequestRepositoryInterface
{
    use PasswordResetRequestRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetRequest::class);
    }

    public function createPasswordResetRequest(
        object $user,
        \DateTimeInterface $expiresAt,
        string $selector,
        string $hashedToken
    ): \SymfonyCasts\Bundle\ResetPassword\Persistence\PasswordResetRequest {
        // TODO: Implement createPasswordResetRequest() method.
    }

    public function persistPasswordResetRequest(PasswordResetRequestInterface $passwordResetRequest)
    {
        // TODO: Implement persistPasswordResetRequest() method.
    }

    public function findPasswordResetRequest(string $selector): ?PasswordResetRequestInterface
    {
        // TODO: Implement findPasswordResetRequest() method.
    }

    public function getMostRecentNonExpiredRequestDate(object $user): ?\DateTimeInterface
    {
        // TODO: Implement getMostRecentNonExpiredRequestDate() method.
    }
}
