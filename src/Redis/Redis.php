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

class Redis
{
	/**
	 * @description REDIS Connnection
	 *
	 * @var \Redis
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
		$this->connection = new \Redis();
	}

	/**
	 * @description connenct to server
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
        if (!$this->connection->isConnected()) {
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
        return $this->connection->getLastError();
	}

    public function __destruct()
    {
        $this->connection->close();
    }
}
