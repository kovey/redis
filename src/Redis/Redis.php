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
use Kovey\Logger\Redis as RedisLogger;
use Kovey\Library\Trace\TraceInterface;

class Redis implements RedisInterface, TraceInterface
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

    /**
     * @description is dev
     *
     * @var bool
     */
    private bool $isDev;

    private string $traceId;

    private string $spanId;

    public function __construct(Array $config)
    {
        foreach (array('host', 'port', 'db') as $field) {
            if (!isset($config[$field])) {
                throw new \RuntimeException("$field is not exists");
            }
        }

        $this->config = $config;
        $this->isDev = ($config['dev'] ?? 'Off') === 'On';

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
        $begin = 0;
        if ($this->isDev) {
            $begin = microtime(true);
        }

        if (!$this->connection->isConnected()) {
            $this->connect();
        }

        try {
            $result = $this->connection->$name(...$params);
        } catch (\RedisException $e) {
            $this->connect();
            $result = $this->connection->$name(...$params);
        } finally {
            if ($this->isDev) {
                RedisLogger::write($name, $params, $result ?? null, microtime(true) - $begin, $this->traceId ?? '', $this->spanId ?? '');
            }
        }

        return $result;
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

    /**
     * @description set traceId
     *
     * @return void
     */
    public function setTraceId(string $traceId) : void
    {
        $this->traceId = $traceId;
    }

    /**
     * @description set spanId
     *
     * @return void
     */
    public function setSpanId(string $spanId) : void
    {
        $this->spanId = $spanId;
    }

    /**
     * @description get traceId
     *
     * @return string
     */
    public function getTraceId() : string
    {
        return $this->traceId;
    }

    /**
     * @description get span id 
     *
     * @return string
     */
    public function getSpanId() : string
    {
        return $this->spanId;
    }
}
