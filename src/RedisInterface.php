<?php
/**
 * @description Redis Interface
 *
 * @package Redis
 *
 * @author kovey
 *
 * @time 2020-10-11 16:56:16
 *
 */
namespace Kovey\Redis;

interface RedisInterface
{
    /**
     * @description construct
     *
     * @param Array $config
     *
     * @return RedisInterface
     */
    public function __construct(Array $config);

    /**
     * @description connect to server
     *
     * @return bool
     */
    public function connect() : bool;

    /**
     * @description get error msg
     *
     * @return string
     */
    public function getError() : string;
}
