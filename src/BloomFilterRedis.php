<?php

namespace Fuliang\BloomFilter;

abstract class BloomFilterRedis
{
    protected string $bucket;

    protected array $hashFunction;

    private BloomFilterHash $Hash;

    private \Redis $Redis;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        if (! $this->bucket) {
            throw new \Exception('需要定义bucket和hashFunction', 500);
        }
        $this->Hash  = new BloomFilterHash();
        $this->Redis = $this->getRedisClient();
    }

    /**
     * 添加到集合中.
     * @return array|false|\Redis
     * @throws \RedisException
     */
    public function add(string $string)
    {
        $pipe = $this->Redis->multi();
        foreach ($this->hashFunction as $function) {
            $hash = $this->Hash->{$function}($string);
            $pipe->setBit($this->bucket, $hash, 1);
        }
        return $pipe->exec();
    }

    /**
     * 从集合中删除.
     * @return array|false|\Redis
     * @throws \RedisException
     */
    public function delete(string $string)
    {
        $pipe = $this->Redis->multi();
        foreach ($this->hashFunction as $function) {
            $hash = $this->Hash->{$function}($string);
            $pipe->setBit($this->bucket, $hash, 0);
        }
        return $pipe->exec();
    }

    /**
     * 查询是否存在, 存在的一定会存在, 不存在有一定几率会误判.
     * @throws \RedisException
     */
    public function exists(string $string): bool
    {
        $pipe = $this->Redis->multi();
        foreach ($this->hashFunction as $function) {
            $hash = $this->Hash->{$function}($string);
            $pipe = $pipe->getBit($this->bucket, $hash);
        }
        $res = $pipe->exec();
        foreach ($res as $bit) {
            if ($bit == 0) {
                return false;
            }
        }
        return true;
    }

    abstract protected function getRedisClient(): \Redis;
}
