<?php

namespace SK\VideoModule\Cache;

use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\filters\PageCache as BasePageCache;
use yii\web\Response;

/**
 * Class PageCache
 *
 * @package SK\VideoModule\Cache
 */
class PageCache extends BasePageCache
{
    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function beforeCacheResponse()
    {
        $response = Instance::ensure(Response::class);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            return false;
        }

        return true;
    }
}
