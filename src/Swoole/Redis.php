<?php
/**
 *
 * @description Redis client, base on\Swoole\Coroutine\Redis
 *
 * @package     Redis
 *
 * @time        Tue Sep 24 09:01:39 2019
 *
 * @class       vendor/Kovey/Components/Cache/Redis.php
 *
 * @author      kovey
 */
namespace Kovey\Redis\Swoole;

class Redis
{
	/**
	 * @description REDIS connection
	 *
	 * @var \Swoole\Coroutine\Redis
	 */
	private $connection;

	/**
	 * @description config
	 *
	 * @var Array
	 */
	private $config;

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
	public function connect()
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
	public function __call($name, $params)
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
	public function getError()
	{
		return sprintf('[%s]: %s', $this->connection->errCode, $this->connection->errMsg);
	}

    public function __destruct()
    {
        $this->connection->close();
    }
}
