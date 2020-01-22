<?php
namespace SK\VideoModule\Sitemap;

use RS\Component\Core\Settings\SettingsInterface;
use samdark\sitemap\Sitemap;
use SK\SeoModule\Sitemap\SitemapHandlerInterface;
use SK\VideoModule\Model\Video;
use Yii;

class VideosSitemapGenerator implements SitemapHandlerInterface
{
    private $filename = 'videos.xml';
    private $urlManager;

    public function __construct(SettingsInterface $settings)
    {
        $siteUrl = $settings->get('site_url');

        $this->urlManager = Yii::$app->urlManager;
        $this->urlManager->setScriptUrl('/web/index.php');
        $this->urlManager->setHostInfo($siteUrl);
    }

    public function create(Sitemap $sitemap)
    {
        $models = Video::find()
            ->alias('v')
            ->select(['v.video_id', 'v.slug', 'v.published_at', 'v.noindex'])
            ->untilNow()
            ->onlyActive()
            ->orderBy(['v.published_at' => SORT_DESC]);

        foreach ($models->batch(200) as $videos) {
            foreach ($videos as $video) {
                if (false === (bool) $video->noindex) {
                    $sitemap->addItem($this->urlManager->createAbsoluteUrl(['/videos/view/index', 'slug' => $video->slug]), strtotime($video->published_at), $sitemap::DAILY, 0.5);
                }
            }
        }
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
