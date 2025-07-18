<?php

namespace Gravitycar\src;

use Gravitycar\exceptions\GCException;
use Gravitycar\Gravitons\Users\Users;
use Gravitycar\lib\DBConnector;
use Gravitycar\lib\Config;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

require_once 'vendor/autoload.php';

class GCFoundation
{
    private static ?GCFoundation $instance = null;
    private DBConnector $db;
    private Config $config;
    private Logger $logger;
    private string $environment;

    private Users $currentUser;

    private function __construct()
    {
        $this->config = new Config();
        $this->setEnvironment();
        $this->db = new DBConnector($this->getDBConnectionParamsForEnvironment());

        $this->logger = new Logger('gravitycar');
        $this->logger->pushHandler(new StreamHandler('gravitycar.log', Level::Debug));
    }


    public function currentUserIsSet(): bool
    {
        return isset($this->currentUser) && $this->currentUser instanceof Users && !empty($this->currentUser->get('id'));
    }

    public function getCurrentUser(): Users
    {
        if (isset($this->currentUser)) {
            return $this->currentUser;
        }
        throw new GCException("Current user is not set", 401);
    }


    public function setCurrentUser(Users $user): void
    {
        $this->currentUser = $user;
    }

    public static function getInstance(): GCFoundation
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function setEnvironment(): void
    {
        if (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] === 'gravitycar.com') {
            $this->environment = 'prod';
        } else {
            $this->environment = 'local';
        }
    }

    public function getDB(): DBConnector
    {
        return $this->db;
    }

    public function log(string $msg, string $level = 'info'): void
    {
        switch (strtolower($level)) {
            case 'debug':
                $this->logger->debug($msg);
                break;
            case 'warning':
                $this->logger->warning($msg);
                break;
            case 'error':
                $this->logger->error($msg);
                break;
            case 'critical':
                $this->logger->critical($msg);
                break;
            case 'info':
            default:
                $this->logger->info($msg);
                break;
        }
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getDBConnectionParamsForEnvironment(): array
    {
        return $this->config->get('db_settings')[$this->environment];
    }

    private function __clone() {}
    public function __wakeup() {}
}