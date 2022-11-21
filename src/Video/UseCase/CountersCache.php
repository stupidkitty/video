<?php

namespace SK\VideoModule\Video\UseCase;

use Redis;
use RedisException;

class CountersCache
{
    private Redis $redis;

    /**
     * CountersCache constructor
     *
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    /**
     * Register a video view by id
     *
     * @param int $id
     * @return void
     * @throws RedisException
     */
    public function view(int $id): void
    {
        $this->rememberId('viewed', $id);
    }

    /**
     * Gets viewed video ids as array. Like
     * ```
     * [
     *     (int) video_id => (int) counter
     *     ...
     * ]
     * ```
     *
     * @return array
     * @throws RedisException
     */
    public function getWithRemovalViewed(): array
    {
        return $this->getWithRemoval('viewed');
    }

    /**
     * Register a video like by id
     *
     * @param int $id
     * @return void
     * @throws RedisException
     */
    public function like(int $id): void
    {
        $this->rememberId('liked', $id);
    }

    /**
     * Gets liked video ids as array. Like
     * ```
     * [
     *     (int) video_id => (int) counter
     *     ...
     * ]
     * ```
     *
     * @return array
     * @throws RedisException
     */
    public function getWithRemovalLiked(): array
    {
        return $this->getWithRemoval('liked');
    }

    /**
     * Register a video dislike by id
     *
     * @param int $id
     * @return void
     * @throws RedisException
     */
    public function dislike(int $id): void
    {
        $this->rememberId('disliked', $id);
    }

    /**
     * Gets disliked video ids as array. Like
     * ```
     * [
     *     (int) video_id => (int) counter
     *     ...
     * ]
     * ```
     *
     * @return array
     * @throws RedisException
     */
    public function getWithRemovalDisliked(): array
    {
        return $this->getWithRemoval('disliked');
    }

    /**
     * Gets video ids by counter (viewed, liked, disliked ... etc.)
     *
     * @param string $counter
     * @return array
     * @throws RedisException
     */
    private function getWithRemoval(string $counter): array
    {
        $counters = [];
        while ($id = $this->redis->rPop("videos:counter:{$counter}")) {
            $id = (int) $id;

            if (isset($counters[$id])) {
                $counters[$id] += 1;
            } else {
                $counters[$id] = 1;
            }
        }

        return $counters;
    }

    /**
     * Push the video id in the counter list
     *
     * @param string $counter
     * @param int $id
     * @return void
     * @throws RedisException
     */
    private function rememberId(string $counter, int $id): void
    {
        if ($id > 0) {
            $this->redis->lPush("videos:counter:{$counter}", $id);
        }
    }
}
