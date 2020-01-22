<?php
namespace SK\VideoModule\Sitemap;

use RS\Component\Core\Settings\SettingsInterface;
use samdark\sitemap\Sitemap;
use SK\SeoModule\Sitemap\SitemapHandlerInterface;
use SK\VideoModule\Model\Category;
use Yii;

class CategoriesSitemapGenerator implements SitemapHandlerInterface
{
    private $filename = 'videos_categories.xml';
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
        $models = Category::find()
            ->select(['category_id', 'slug', 'updated_at'])
            ->where(['enabled' => 1]);

        foreach ($models->batch(200) as $categories) {
            foreach ($categories as $category) {
                $sitemap->addItem($this->urlManager->createAbsoluteUrl(['/videos/category/ctr', 'slug' => $category->slug]), strtotime($category->updated_at), $sitemap::DAILY, 0.7);
            }
        }
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
