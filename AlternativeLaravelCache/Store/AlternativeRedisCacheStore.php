<?php

namespace AlternativeLaravelCache\Store;

use AlternativeLaravelCache\Core\AlternativeCacheStore;
use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Predis\PredisCachePool;
use Cache\Adapter\Redis\RedisCachePool;
use Cache\Hierarchy\HierarchicalPoolInterface;
use Illuminate\Redis\RedisManager;

/**
 * @method RedisManager getDb()
 */
class AlternativeRedisCacheStore extends AlternativeCacheStore {
    
    /**
     * The Redis database connection.
     *
     * @var RedisManager
     */
    protected $db;
    
    /**
     * Wrap Redis connection with PredisCachePool
     *
     * @return PredisCachePool|RedisCachePool|AbstractCachePool
     */
    public function wrapConnection() {
        if ($this->isPhpRedis()) {
            // PHPRedis extension client
            return new RedisCachePool($this->getConnection());
        }
    
        return new PredisCachePool($this->getConnection());
    }
    
    protected function isPhpRedis(): bool {
        $connectionClass = get_class($this->getConnection());
        return $connectionClass === 'Redis' || $connectionClass === 'RedisCluster';
    }
    
    /**
     * Get the Redis connection client
     *
     * @return \Predis\Client|\Predis\ClientInterface|\Redis
     */
    public function getConnection() {
        return $this
            ->getDb()
            ->connection($this->connection)
            ->client();
    }
    
    public function setPrefix($prefix) {
        // not allowed chars: "{}()/\@"
        parent::setPrefix(preg_replace('%[{}()/@:\\\]%', '_', $prefix));
    }
    
    public function fixItemKey($key) {
        // not allowed characters: {}()/\@:
        return preg_replace('%[{}()/@:\\\]%', '-', parent::fixItemKey($key));
    }
    
    public function getHierarchySeparator() {
        return HierarchicalPoolInterface::HIERARCHY_SEPARATOR;
    }
    
}
