<?php

namespace Gravitycar\Gravitons\Users;

use Gravitycar\Gravitons\Graviton;

class Users extends Graviton
{
    protected bool $is_admin = false;
    protected string $table = 'users';
    protected string $type = 'Users';
    protected string $label = 'Users';
    protected string $labelSingular = 'User';
    protected array $templates = ['base', 'person'];

    public function __construct()
    {
        parent::__construct();
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function getUserTypes(): array
    {
        return ['admin' => 'Administrator', 'regular' => 'Regular User'];
    }

    public function getName(): string
    {
        return trim($this->get('first_name') . ' ' . $this->get('last_name'));
    }

    public function setAdmin(): void
    {
        $this->is_admin = ($this->get('user_type') == 'admin');
    }


    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }


    public function setLastLogin(): void
    {
        $this->set('last_login', $this->getCurrentDateTime());
    }
}