<?php

namespace Choi\Doctrine;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class DoctrineServiceProvider extends ServiceProvider
{
    /**
     * Database connections
     *
     * @var array
     */
    protected $connections;

    /**
     * Default database connection
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $connection;

    /**
     * Configuration
     *
     * @var \Doctrine\ORM\Configuration
     */
    protected $config;


    /**
     * Booting the service provider
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/doctrine.php' => config_path('choi/doctrine.php'),
        ]);
    }

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/doctrine.php', 'choi.doctrine');

        $this->registerConnections();
        $this->registerConfig();
        $this->registerEntityManager();
    }

    /**
     * Register connections
     *
     * @return void
     */
    protected function registerConnections()
    {
        // Convert Laravel connections to Doctrine-style
        $connections = Config::get('database.connections');
        foreach ($connections as $key => $config) {
            $connections[$key] = $this->convertConnectionConfig($config);
        }
        $this->connections = $connections;

        // Get default connection
        $this->connection = $this->getConnection(Config::get('database.default'));
    }

    /**
     * Connection getter
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function getConnection($connection)
    {
        return DriverManager::getConnection($this->connections[$connection]);
    }

    /**
     * Convert Laravel 5 connection config to Doctrine config
     * @see http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html
     *
     * @param array $config
     * @throws InvalidDriverException
     * @return array
     */
    protected function convertConnectionConfig(array $config)
    {
        $driver = $this->getConfigValue($config, 'driver');
        switch($driver) {
            case 'sqlite':
                return [
                    'driver'   => 'pdo_sql',
                    'user'     => $this->getConfigValue($config, 'username'),
                    'password' => $this->getConfigValue($config, 'password'),
                    'path'     => $this->getConfigValue($config, 'database'),
                    'memory'   => false,
                ];
                break;

            case 'mysql':
                return [
                    'driver'      => 'pdo_mysql',
                    'user'        => $this->getConfigValue($config, 'username'),
                    'password'    => $this->getConfigValue($config, 'password'),
                    'host'        => $this->getConfigValue($config, 'host'),
                    'port'        => $this->getConfigValue($config, 'port'),
                    'dbname'      => $this->getConfigValue($config, 'database'),
                    'unix_socket' => $this->getConfigValue($config, 'unix_socket'),
                    'charset'     => $this->getConfigValue($config, 'charset', 'utf8'),
                ];
                break;

            case 'pgsql':
                return [
                    'driver'   => 'pdo_pgsql',
                    'user'     => $this->getConfigValue($config, 'username'),
                    'password' => $this->getConfigValue($config, 'password'),
                    'host'     => $this->getConfigValue($config, 'host'),
                    'port'     => $this->getConfigValue($config, 'port'),
                    'dbname'   => $this->getConfigValue($config, 'database'),
                    'charset'  => $this->getConfigValue($config, 'charset', 'utf8'),
                    'sslmode'  => $this->getConfigValue($config, 'sslmode'),
                ];
                break;

            case 'sqlsrv':
                return [
                    'driver'   => 'pdo_sqlsrv',
                    'user'     => $this->getConfigValue($config, 'username'),
                    'password' => $this->getConfigValue($config, 'password'),
                    'host'     => $this->getConfigValue($config, 'host'),
                    'port'     => $this->getConfigValue($config, 'port'),
                    'dbname'   => $this->getConfigValue($config, 'database'),
                ];
                break;

            default:
                throw new InvalidDriverException('Invalid driver ['.$driver.']');
                break;
        }
    }

    /**
     * Config value getter
     *
     * @return mixed
     */
    protected function getConfigValue(array $config, $key, $default = null)
    {
        return isset($config[$key]) ? $config[$key] : $default;
    }

    /**
     * Register config
     * @see http://www.doctrine-project.org/api/orm/2.4/class-Doctrine.ORM.Tools.Setup.html
     *
     * @param void
     * @throws InvalidMapperException
     * @return void
     */
    protected function registerConfig()
    {
        $config = Config::get('choi.doctrine');
        switch($config['mapper']) {
            case 'annotation':
            case 'docblock':
                $method = 'createAnnotationMetadataConfiguration';
                break;

            case 'xml':
                $method = 'createXMLMetadataConfiguration';
                break;

            case 'yaml':
                $method = 'createYAMLMetadataConfiguration';
                break;

            default:
                throw new InvalidMapperException('Invalid mapper ['.$config['mapper'].']');
                break;
        }

        $this->config = Setup::{$method}($config['paths'], env('APP_DEBUG'));
    }

    /**
     * Register entity manager
     *
     * @return void
     */
    protected function registerEntityManager()
    {
        $this->app->singleton('choi.doctrine.entitymanager', function() {
            return EntityManager::create($this->connection, $this->config);
        });
    }
}
