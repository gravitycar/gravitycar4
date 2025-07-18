<?php

namespace Gravitycar\src\Auth;

use Exception;
use Gravitycar\exceptions\GCException;
use Gravitycar\Gravitons\Users\Users;
use Gravitycar\lib\DBConnector;
use Gravitycar\src\GCFoundation;


/**
 * Authentication class for handling user authentication and session management
 * @package Gravitycar\src\Auth
 *
 * usage example from copilot:
 * // Login example
    $auth = new \Gravitycar\src\Auth\Authentication();

    if ($_POST['username'] && $_POST['password']) {
        if ($auth->authenticate($_POST['username'], $_POST['password'])) {
            header('Location: /dashboard.php');
        } else {
            echo "Invalid credentials";
        }
    }

    // Protect a page
    $auth->requireAuth();

    // Check if admin
    if ($auth->isAdmin()) {
        // Admin-only content
    }
 */

class Authentication
{
    private GCFoundation $app;
    private Users $user;
    private array $sessionData;

    private DBConnector $db;

    public function __construct()
    {
        $this->app = GCFoundation::getInstance();
        $this->db = $this->app->getDB();
        $this->user = new Users();
        $this->sessionData = $_SESSION ?? [];

        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Authenticate user with username and password
     * @throws GCException
     */
    public function authenticate(string $username, string $password): bool
    {
        try {
            // Find user by username
            $userData = $this->findUserByUsername($username);

            if (!$userData) {
                return false;
            }

            // Verify password
            if ($this->verifyPassword($password, $userData['password'])) {
                $this->setUserSession($userData);
                $currentUser = $this->getCurrentUser();
                $this->app->setCurrentUser($currentUser);
                $currentUser->setLastLogin();
                return true;
            }

            return false;
        } catch (Exception $e) {
            throw GCException::convert($e);
        }
    }

    /**
     * Find user by username
     * @throws GCException
     */
    private function findUserByUsername(string $username): ?array
    {
        $queryBuilder = $this->db->getConnection()->createQueryBuilder();
        $queryBuilder->select('*')
                     ->from($this->user->getTable())
                     ->where('username = :username')
                     ->andWhere('deleted = :deleted')
                     ->setParameter('username', $this->db->sanitize($username))
                     ->setParameter('deleted', 0);

        try {
            $result = $queryBuilder->executeQuery();
            $userData = $result->fetchAssociative();
            return $userData ?: null;
        } catch (Exception $e) {
            throw GCException::convert($e);
        }
    }

    /**
     * Verify password - handles both hashed and plain text for backward compatibility
     */
    private function verifyPassword(string $inputPassword, string $storedPassword): bool
    {
        // Check if stored password is already hashed
        if (password_get_info($storedPassword)['algo'] !== null) {
            return password_verify($inputPassword, $storedPassword);
        }

        // For backward compatibility with plain text passwords
        if ($inputPassword === $storedPassword) {
            // Upgrade to hashed password for security
            $this->upgradePasswordHash($inputPassword, $storedPassword);
            return true;
        }

        return false;
    }

    /**
     * Upgrade plain text password to hashed version
     */
    private function upgradePasswordHash(string $plainPassword, string $currentPassword): void
    {
        try {
            $user = new Users();
            $hashedPassword = -$user->hashPassword($plainPassword);

            $queryBuilder = $this->db->getConnection()->createQueryBuilder();
            $queryBuilder->update($this->user->getTable())
                         ->set('password', ':password')
                         ->where('password = :currentPassword')
                         ->setParameter('password', $hashedPassword)
                         ->setParameter('currentPassword', $currentPassword);

            $queryBuilder->executeStatement();
        } catch (Exception $e) {
            error_log("Failed to upgrade password hash: " . $e->getMessage());
        }
    }

    /**
     * Set user session data
     */
    private function setUserSession(array $userData): void
    {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['is_admin'] = $userData['is_admin'] ?? false;
        $_SESSION['first_name'] = $userData['first_name'] ?? '';
        $_SESSION['last_name'] = $userData['last_name'] ?? '';
        $_SESSION['authenticated'] = true;
        $_SESSION['login_time'] = time();
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    /**
     * Get current authenticated user
     * @throws GCException
     */
    public function getCurrentUser(): ?Users
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $user = new Users();
        try {
            if ($user->retrieve($_SESSION['user_id'])) {
                return $user;
            }
        } catch (Exception $e) {
            throw GCException::convert($e);
        }

        return null;
    }

    /**
     * Get current user ID
     */
    public function getCurrentUserId(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Check if current user is admin
     */
    public function isAdmin(): bool
    {
        return $this->isAuthenticated() && ($_SESSION['is_admin'] ?? false);
    }

    /**
     * Logout user
     */
    public function logout(): void
    {
        // Clear user-related session data
        unset($_SESSION['user_id']);
        unset($_SESSION['username']);
        unset($_SESSION['is_admin']);
        unset($_SESSION['first_name']);
        unset($_SESSION['last_name']);
        unset($_SESSION['authenticated']);
        unset($_SESSION['login_time']);

        // Regenerate session ID for security
        session_regenerate_id(true);
    }

    /**
     * Require authentication - redirect if not authenticated
     */
    public function requireAuth(string $redirectUrl = '/login.php'): void
    {
        if (!$this->isAuthenticated()) {
            header("Location: $redirectUrl");
            exit();
        }
    }

    /**
     * Require admin access - redirect if not admin
     */
    public function requireAdmin(string $redirectUrl = '/unauthorized.php'): void
    {
        $this->requireAuth();

        if (!$this->isAdmin()) {
            header("Location: $redirectUrl");
            exit();
        }
    }

    /**
     * Hash password for storage
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function generateSessionToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}