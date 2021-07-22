<?php
/**
 * @description
 *
 * @package
 *
 * @author kovey
 *
 * @time 2020-10-21 15:41:26
 *
 */
namespace Kovey\Redis\Redis;

use PHPUnit\Framework\TestCase;

class RedisTest extends TestCase
{
    public function testRedis()
    {
        $redis = new Redis(array(
            'host' => '127.0.0.1',
            'port' => 6379,
            'db' => 0
        ));
        $this->assertTrue($redis->connect());
        $redis->set('test', 'kovey redis');
        $this->assertEquals('kovey redis', $redis->get('test'));
        $redis->del('test');
        $this->assertFalse($redis->get('test'));

        $redis->hSet('test_hash', 'kovey', 'redis');
        $this->assertEquals('redis', $redis->hGet('test_hash', 'kovey'));
        $redis->del('test_hash');
        $this->assertFalse($redis->hGet('test_hash', 'kovey'));

        $time = time();
        $redis->zAdd('test_zset', $time, 'kovey');
        $this->assertEquals(array('kovey'), $redis->zRange('test_zset', 0, -1));
        $redis->del('test_zset');
        $this->assertEquals(array(), $redis->zRange('test_zset', 0, -1));

        $redis->lPush('test_list', 'kovey');
        $redis->lPush('test_list', 'framework');

        $this->assertEquals('kovey', $redis->rPop('test_list'));
        $this->assertEquals('framework', $redis->rPop('test_list'));
        $this->assertFalse($redis->rPop('test_list'));
        $redis->del('test_list');
    }

    public function testRedisFailure()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('host is not exists');
        $redis = new Redis(array(
            'aa' => '1234',
            'bb' => 'aa',
            'c' => 0
        ));
    }

    public function testRedisConnectFailure()
    {
        $redis = new Redis(array(
            'host' => '127.0.0.2',
            'port' => 6379,
            'db' => 0
        ));

        $this->assertFalse($redis->connect());
    }
}
