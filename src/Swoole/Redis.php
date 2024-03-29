<?php
/**
 *
 * @description Redis client, base on\Swoole\Coroutine\Redis
 *
 * @package     Redis
 *
 * @time        Tue Sep 24 09:01:39 2019
 *
 * @author      kovey
 */
namespace Kovey\Redis\Swoole;

use Kovey\Redis\RedisInterface;
use Kovey\Logger\Redis as RedisLogger;
use Kovey\Library\Trace\TraceInterface;

class Redis implements RedisInterface, TraceInterface
{
    /**
     * @description REDIS connection
     *
     * @var \Swoole\Coroutine\Redis
     */
    private \Swoole\Coroutine\Redis $connection;

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
        $this->connection = new \Swoole\Coroutine\Redis();
        $this->connection->setOptions(array(
            'compatibility_mode' => true
        ));
    }

    /**
     * @description connect to server
     *
     * @return bool
     */
    public function connect() : bool
    {
        if (!$this->connection->connect($this->config['host'], $this->config['port'])) {
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

        try {
            if (!$this->connection->connected) {
                $this->connect();
            }

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
        return sprintf('[%s]: %s', $this->connection->errCode, $this->connection->errMsg);
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
    
    public function setTraceId(string $traceId) : void
    {
        $this->traceId = $traceId;
    }

    public function setSpanId(string $spanId) : void
    {
        $this->spanId = $spanId;
    }

    public function getTraceId() : string
    {
        return $this->traceId;
    }

    public function getSpanId() : string
    {
        return $this->spanId;
    }
}
