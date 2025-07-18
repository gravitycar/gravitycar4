<?php

namespace Gravitycar\Gravitons\Users\api;

use Gravitycar\exceptions\GCException;
use Gravitycar\Gravitons\Users\Users;
use Gravitycar\src\API\BaseAPIController;
use Gravitycar\src\Auth\Authentication;

class UsersAPIController extends BaseAPIController
{
    public function __construct()
    {
        parent::__construct(Users::class);
    }

    /**
     * POST /api/auth/login
     */
    public function login(array $params): array
    {
        $router = new \Gravitycar\src\API\APIRouter();
        $data = $router->getRequestData();

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (!$username || !$password) {
            throw new GCException('Username and password required');
        }

        if ($this->auth->authenticate($username, $password)) {
            $user = $this->auth->getCurrentUser();
            $user->setLastLogin();
            $user->save();

            return [
                'message' => 'Login successful',
                'user' => $this->formatItem($user),
                'token' => $this->generateAPIToken($user)
            ];
        }

        throw new GCException('Invalid credentials');
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(array $params): array
    {
        $this->auth->logout();

        return ['message' => 'Logout successful'];
    }

    /**
     * GET /api/auth/me
     */
    public function getCurrentUser(array $params): array
    {
        $user = $this->auth->getCurrentUser();

        if (!$user) {
            throw new GCException('User not authenticated');
        }

        return $this->formatItem($user);
    }

    /**
     * POST /api/auth/change-password
     */
    public function changePassword(array $params): array
    {
        $user = $this->auth->getCurrentUser();

        if (!$user) {
            throw new GCException('User not authenticated');
        }

        $router = new \Gravitycar\src\API\APIRouter();
        $data = $router->getRequestData();

        $currentPassword = $data['current_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';

        if (!$currentPassword || !$newPassword) {
            throw new GCException('Current password and new password required');
        }

        // Verify current password
        if (!password_verify($currentPassword, $user->get('password'))) {
            throw new GCException('Current password is incorrect');
        }

        // Update password
        $user->set('password', $user->hashPassword($newPassword));
        $user->set('date_updated', $user->getCurrentDateTime());

        if (!$user->save()) {
            throw new GCException('Failed to update password');
        }

        return ['message' => 'Password updated successfully'];
    }

    /**
     * Override store method to hash password
     */
    public function store(array $params): array
    {
        $router = new \Gravitycar\src\API\APIRouter();
        $data = $router->getRequestData();

        /** @var Users $user */
        $user = new Users();

        foreach ($data as $field => $value) {
            if ($user->hasField($field)) {
                if ($field === 'password') {
                    $user->set($field, $user->hashPassword($value));
                } else {
                    $user->set($field, $value);
                }
            }
        }

        $user->set('date_created', $user->getCurrentDateTime());
        $user->set('date_updated', $user->getCurrentDateTime());

        if (!$user->save()) {
            throw new GCException('Failed to create user');
        }

        return $this->formatItem($user);
    }

    private function generateAPIToken(Users $user): string
    {
        // Simple token generation - implement JWT for production
        return base64_encode($user->get('id') . ':' . time() . ':' . uniqid());
    }
}