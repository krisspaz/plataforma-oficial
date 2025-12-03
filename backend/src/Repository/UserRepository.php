<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Find user by email
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Find active users by role
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->where('JSON_CONTAINS(u.roles, :role) = 1')
            ->andWhere('u.isActive = :active')
            ->setParameter('role', json_encode($role))
            ->setParameter('active', true)
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all active users
     */
    public function findActive(): array
    {
        return $this->findBy(['isActive' => true], ['lastName' => 'ASC']);
    }

    /**
     * Search users by name or email
     */
    public function search(string $query): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.firstName LIKE :query')
            ->orWhere('u.lastName LIKE :query')
            ->orWhere('u.email LIKE :query')
            ->andWhere('u.isActive = :active')
            ->setParameter('query', '%' . $query . '%')
            ->setParameter('active', true)
            ->orderBy('u.lastName', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
