<?php
/**
 * @description Redis client, base on phpredis extension
 *
 * @package Redis
 *
 * @author kovey
 *
 * @time 2020-09-16 15:50:13
 *
 */
namespace Kovey\Redis\Redis;

use Kovey\Redis\RedisInterface;

class Redis implements RedisInterface
{
    /**
     * @description REDIS Connection
     *
     * @var \Redis
     */
    private \Redis $connection;

    /**
     * @description config
     *
     * @var Array
     */
    private Array $config;

    public function __construct(Array $config)
    {
        foreach (array('host', 'port', 'db') as $field) {
            if (!isset($config[$field])) {
                throw new \RuntimeException("$field is not exists");
            }
        }

        $this->config = $config;
        $this->connection = new \Redis();
    }

    /**
     * @description connenct to server
     *
     * @return bool
     *
     * @throws RedisException
     */
    public function connect() : bool
    {
        try {
            if (!empty($this->config['username']) && !empty($this->config['password'])) {
                if (!$this->connection->connect($this->config['host'], $this->config['port'], 1, null, 0, 0, array(
                    'auth' => array($this->config['username'], $this->config['password'])
                ))) {
                    return false;
                }
            } else {
                if (!$this->connection->connect($this->config['host'], $this->config['port'], 1)) {
                    return false;
                }
            }
        } catch (\RedisException $e) {
            return false;
        }

        return $this->connection->select($this->config['db']);
    }

    /**
     * @description run command
     *
     * @param string $name
     *
     * @param Array $params
     *
     * @return mixed
     */
    public function __call(string $name, Array $params) : mixed
    {
        if (!$this->connection->isConnected()) {
            $this->connect();
        }

        try {
            return $this->connection->$name(...$params);
        } catch (\RedisException $e) {
            $this->connect();
            return $this->connection->$name(...$params);
        }
    }

    /**
     * @description get error
     *
     * @return string
     */
    public function getError() : string
    {
        return $this->connection->getLastError();
    }

    /**
     * @description close connection
     *
     * @return null
     */
    public function __destruct()
    {
        $this->connection->close();
    }
}
