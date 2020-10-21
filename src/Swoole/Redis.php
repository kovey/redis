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

class Redis implements RedisInterface
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

	public function __construct(Array $config)
	{
		$this->config = $config;
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
	public function __call(string $name, Array $params)
	{
		if (!$this->connection->connected) {
			$this->connect();
		}

		return $this->connection->$name(...$params);
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
}
