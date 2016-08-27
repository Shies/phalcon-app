<?php

namespace Engine\Cache;

use Phalcon\Cache\Backend;
use Phalcon\Cache\BackendInterface;
use Phalcon\Cache\Exception;

/**
 * Phalcon\Cache\Backend\Redis
 *
 * Allows to cache output fragments, PHP data or raw data to a redis backend
 *
 * This adapter uses the special redis key "_PHCR" to store all the keys internally used by the adapter
 *
 *<code>
 *
 * // Cache data for 2 days
 * $frontCache = new \Phalcon\Cache\Frontend\Data(array(
 *    "lifetime" => 172800
 * ));
 *
 * //Create the Cache setting redis connection options
 * $cache = new Phalcon\Cache\Backend\Redis($frontCache, array(
 *        'host' => 'localhost',
 *        'port' => 6379,
 *        'auth' => 'foobared',
 *    'persistent' => false
 * ));
 *
 * //Cache arbitrary data
 * $cache->save('my-data', array(1, 2, 3, 4, 5));
 *
 * //Get data
 * $data = $cache->get('my-data');
 *
 *</code>
 */
class Redis extends Backend implements BackendInterface
{

    protected $_redis = null;

    /**
     * Phalcon\Cache\Backend\Redis constructor
     *
     * @param  \Phalcon\Cache\FrontendInterface $frontend
     * @param  array $options
     * @throws \Phalcon\Cache\Exception
     */
    public function __construct($frontend, $options = null)
    {
        if (!is_array($options)) {
            $options = [];
        }

        if (!isset($options["host"])) {
            $options["host"] = "127.0.0.1";
        }

        if (!isset($options["port"])) {
            $options["port"] = 6379;
        }

        if (!isset($options["persistent"])) {
            $options["persistent"] = false;
        }

        if (!isset($options["statsKey"]) || empty($options["statsKey"])) {
            $options["statsKey"] = "_PHCR";
        }

        parent::__construct($frontend, $options);
    }

    /**
     * Create internal connection to redis
     */
    public function _connect()
    {
        $options = $this->_options;
        $redis = new \Redis();

        if (!isset($options["host"]) || !isset($options["port"]) || !isset($options["persistent"])) {
            throw new Exception("Unexpected inconsistency in options");
        }

        if ($options["persistent"]) {
            $success = $redis->pconnect($options["host"], $options["port"]);
        } else {
            $success = $redis->connect($options["host"], $options["port"]);
        }

        if (!$success) {
            throw new Exception("Cannot connect to Redisd server");
        }
        if (isset($options["auth"])) {
            $success = $redis->auth($options["auth"]);

            if (!$success) {
                throw new Exception("Redisd server is authentication failed");
            }
        }

        $this->_redis = $redis;
    }

    /**
     * Returns a cached content
     *
     * @param int|string keyName
     * @param   long lifetime
     * @return  mixed
     */
    public function get($keyName, $lifetime = null)
    {
        $redis = $this->_redis;
        if (!is_object($redis)) {
            $this->_connect();
            $redis = $this->_redis;
        }

        $frontend = $this->_frontend;
        $prefix = $this->_prefix;
        $lastKey = "_PHCR" . $prefix . $keyName;
        $this->setLastKey($lastKey);
        $cachedContent = $redis->get($lastKey);

        if (!$cachedContent) {
            return null;
        }

        if (is_numeric($cachedContent)) {
            return $cachedContent;
        }

        return $frontend->afterRetrieve($cachedContent);
    }

    /**
     * Stores cached content into the file backend and stops the frontend
     *
     * @param int|string keyName
     * @param string content
     * @param long lifetime
     * @param boolean stopBuffer
     */
    public function save($keyName = null, $content = null, $lifetime = null, $stopBuffer = true)
    {
        if (!$keyName) {
            $lastKey = $this->_lastKey;
            $prefixedKey = substr($lastKey, 5);
        } else {
            $prefix = $this->_prefix;
            $prefixedKey = $prefix . $keyName;
            $lastKey = "_PHCR" . $prefixedKey;
        }

        if (!$lastKey) {
            throw new Exception("Cache must be started first");
        }

        $frontend = $this->_frontend;

        /**
         * Check if a connection is created or make a new one
         */
        $redis = $this->_redis;
        if (!is_object($redis)) {
            $this->_connect();
            $redis = $this->_redis;
        }
        if (!$content) {
            $cachedContent = $frontend->getContent();
        } else {
            $cachedContent = $content;
        }
        /**
         * Prepare the content in the frontend
         */
        if (!is_numeric($cachedContent)) {
            $preparedContent = $frontend->beforeStore($cachedContent);
        }

        if (!$lifetime) {
            $tmp = $this->_lastLifetime;

            if (!$tmp) {
                $tt1 = $frontend->getLifetime();
            } else {
                $tt1 = $tmp;
            }
        } else {
            $tt1 = $lifetime;
        }

        if (is_numeric($cachedContent)) {
            $success = $redis->set($lastKey, $cachedContent);
        } else {
            $success = $redis->set($lastKey, $preparedContent);
        }

        if (!$success) {
            throw new Exception("Failed storing data in redis");
        }

        $redis->settimeout($lastKey, $tt1);

        $options = $this->_options;

        if (!isset($options["statsKey"])) {
            throw new Exception("Unexpected inconsistency in options");
        }

        $specialKey = $options["statsKey"];

        $redis->sAdd($specialKey, $prefixedKey);

        $isBuffering = $frontend->isBuffering();

        if (!$stopBuffer) {
            $frontend->stop();
        }

        if ($isBuffering == true) {
            echo $cachedContent;
        }

        $this->_started = false;
    }

    /**
     * Deletes a value from the cache by its key
     *
     * @param int|string keyName
     * @return bool
     * @throws Exception
     */
    public function delete($keyName)
    {
        $redis = $this->_redis;
        if (!is_object($redis)) {
            $this->_connect();
            $redis = $this->_redis;
        }

        $prefix = $this->_prefix;
        $prefixedKey = $prefix . $keyName;
        $lastKey = "_PHCR" . $prefixedKey;
        $options = $this->_options;

        if (!isset($options["statsKey"])) {
            throw new Exception("Unexpected inconsistency in options");
        }

        $specialKey = $options["statsKey"];

        $redis->sRem($specialKey, $prefixedKey);

        /**
         * Delete the key from redis
         */
        $redis->delete($lastKey);
    }

    /**
     * Query the existing cached keys
     *
     * @param string prefix
     * @return array
     * @throws Exception
     */
    public function queryKeys($prefix = null)
    {
        $redis = $this->_redis;

        if (!is_object($redis)) {
            $this->_connect();
            $redis = $this->_redis;
        }

        $options = $this->_options;

        if (!isset($options["statsKey"])) {
            throw new Exception("Unexpected inconsistency in options");
        }

        $specialKey = $options["statsKey"];

        /**
         * Get the key from redis
         */
        $keys = $redis->sMembers($specialKey);
        if (!is_array($keys)) {
            foreach ($keys as $key) {
                if ($prefix && !starts_with($key, $prefix)) {
                    unset($keys[$key]);
                }
            }
        }

        return $keys;
    }

    /**
     * Checks if cache exists and it isn't expired
     *
     * @param string keyName
     * @param   long lifetime
     * @return boolean
     */
    public function exists($keyName = null, $lifetime = null)
    {

        if (!$keyName) {
            $lastKey = $this->_lastKey;
        } else {
            $prefix = $this->_prefix;
            $lastKey = "_PHCR" . $prefix . $keyName;
        }

        if ($lastKey) {
            $redis = $this->_redis;
            if (!is_object($redis)) {
                $this->_connect();
                $redis = $this->_redis;
            }

            if (!$redis->get($lastKey)) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Increment of given $keyName by $value
     *
     * @param  string keyName
     * @param  long lifetime
     * @return long
     */
    public function increment($keyName = null, $value = null)
    {

        $redis = $this->_redis;

        if (!is_object($redis)) {
            $this->_connect();
            $redis = $this->_redis;
        }

        if (!$keyName) {
            $lastKey = $this->_lastKey;
        } else {
            $prefix = $this->_prefix;
            $lastKey = "_PHCR" . $prefix . $keyName;
            $this->_lastKey = $lastKey;
        }

        if (!$value) {
            $value = 1;
        }

        return $redis->incrBy($lastKey, $value);
    }

    /**
     * Decrement of $keyName by given $value
     *
     * @param  string keyName
     * @param  long value
     * @return long
     */
    public function decrement($keyName = null, $value = null)
    {


        $redis = $this->_redis;

        if (!is_object($redis)) {
            $this->_connect();
            $redis = $this->_redis;
        }

        if (!$keyName) {
            $lastKey = $this->_lastKey;
        } else {
            $prefix = $this->_prefix;
            $lastKey = "_PHCR" . $prefix . $keyName;
            $this->_lastKey = $lastKey;
        }

        if (!$value) {
            $value = 1;
        }

        return $redis->decrBy($lastKey, $value);
    }

    /**
     * Immediately invalidates all existing items.
     * @return bool
     * @throws Exception
     */
    public function flush()
    {
        $options = $this->_options;

        if (!isset($options["statsKey"])) {
            throw new Exception("Unexpected inconsistency in options");
        }

        $specialKey = $options["statsKey"];

        $redis = $this->_redis;

        if (!is_object($redis)) {
            $this->_connect();
            $redis = $this->_redis;
        }

        $keys = $redis->sMembers($specialKey);
        if (!is_array($keys)) {
            foreach ($keys as $key) {
                $lastKey = "_PHCR" . $key;
                $redis->sRem($specialKey, $key);
                $redis->delete($lastKey);
            }
        }

        return true;
    }

    public function hset($name, $prefix, $data)
    {
        $this->init_master();
        if (!$this->enable || !is_array($data) || empty($data)) return false;
        $this->type = $prefix;
        foreach ($data as $key => $value) {
            if ($value[0] == 'exp') {
                $value[1] = str_replace(' ', '', $value[1]);
                preg_match('/^[A-Za-z_]+([+-]\d+(\.\d+)?)$/', $value[1], $matches);
                if (is_numeric($matches[1])) {
                    $this->hIncrBy($name, $prefix, $key, $matches[1]);
                }
                unset($data[$key]);
            }
        }
        if (count($data) == 1) {
            $this->handler->hset($this->_key($name), key($data), current($data));
        } elseif (count($data) > 1) {
            $this->handler->hMset($this->_key($name), $data);
        }
    }

    public function hget($name, $prefix, $key = null)
    {
        $this->init_slave();
        if (!$this->enable) return false;
        $this->type = $prefix;
        if ($key == '*' || is_null($key)) {
            return $this->handler->hGetAll($this->_key($name));
        } elseif (strpos($key, ',') != false) {
            return $this->handler->hmGet($this->_key($name), explode(',', $key));
        } else {
            return $this->handler->hget($this->_key($name), $key);
        }
    }

    public function hdel($name, $prefix, $key = null)
    {
        $this->init_master();
        if (!$this->enable) return false;
        $this->type = $prefix;
        if (is_null($key)) {
            if (is_array($name)) {
                return $this->handler->delete(array_walk($array, array(self, '_key')));
            } else {
                return $this->handler->delete($this->_key($name));
            }
        } else {
            if (is_array($name)) {
                foreach ($name as $key => $value) {
                    $this->handler->hdel($this->_key($name), $key);
                }
                return true;
            } else {
                return $this->handler->hdel($this->_key($name), $key);
            }
        }
    }

    public function hIncrBy($name, $prefix, $key, $num = 1)
    {
        if ($this->hget($name, $prefix, $key) !== false) {
            $this->handler->hIncrByFloat($this->_key($name), $key, floatval($num));
        }
    }
}
