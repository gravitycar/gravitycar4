<?php

namespace Gravitycar\src\Auth;

use Gravitycar\exceptions\GCException;
use Gravitycar\Gravitons\Users\Users;

class PasswordReset
{
    private Users $user;

    public function __construct()
    {
        $this->user = new Users();
    }

    /**
     * Generate a password reset token and store it in the database
     * @throws GCException
     */
    public function generateResetToken(string $email): string
    {
        $user = $this->findUserByEmail($email);

        if (!$user) {
            throw new GCException("User with email $email not found.");
        }

        $token = bin2hex(random_bytes(32));
        $expiresAt = (new \DateTime())->modify('+1 hour')->format('Y-m-d H:i:s');

        $queryBuilder = $this->user->getDB()->getConnection()->createQueryBuilder();
        $queryBuilder->update($this->user->getTable())
                     ->set('reset_token', ':token')
                     ->set('reset_token_expires', ':expires')
                     ->where('email = :email')
                     ->setParameter('token', $token)
                     ->setParameter('expires', $expiresAt)
                     ->setParameter('email', $email);

        $queryBuilder->executeStatement();

        return $token;
    }

    /**
     * Send the reset token to the user's email
     */
    public function sendResetToken(string $email, string $token): void
    {
        $resetLink = "https://yourdomain.com/reset-password.php?token=$token";

        // Use your mailer library to send the email
        mail($email, "Password Reset", "Click the link to reset your password: $resetLink");
    }

    /**
     * Verify the reset token
     * @throws GCException
     */
    public function verifyResetToken(string $token): bool
    {
        $queryBuilder = $this->user->getDB()->getConnection()->createQueryBuilder();
        $queryBuilder->select('*')
                     ->from($this->user->getTable())
                     ->where('reset_token = :token')
                     ->andWhere('reset_token_expires > :now')
                     ->setParameter('token', $token)
                     ->setParameter('now', (new \DateTime())->format('Y-m-d H:i:s'));

        $result = $queryBuilder->executeQuery()->fetchAssociative();

        if (!$result) {
            throw new GCException("Invalid or expired reset token.");
        }

        return true;
    }

    /**
     * Reset the user's password
     * @throws GCException
     */
    public function resetPassword(string $token, string $newPassword): void
    {
        $this->verifyResetToken($token);

        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $queryBuilder = $this->user->getDB()->getConnection()->createQueryBuilder();
        $queryBuilder->update($this->user->getTable())
                     ->set('password', ':password')
                     ->set('reset_token', 'NULL')
                     ->set('reset_token_expires', 'NULL')
                     ->where('reset_token = :token')
                     ->setParameter('password', $hashedPassword)
                     ->setParameter('token', $token);

        $queryBuilder->executeStatement();
    }

    /**
     * Find user by email
     * @throws GCException
     */
    private function findUserByEmail(string $email): ?array
    {
        $queryBuilder = $this->user->getDB()->getConnection()->createQueryBuilder();
        $queryBuilder->select('*')
                     ->from($this->user->getTable())
                     ->where('email = :email')
                     ->setParameter('email', $email);

        $result = $queryBuilder->executeQuery()->fetchAssociative();

        return $result ?: null;
    }
}