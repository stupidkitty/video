<?php
namespace SK\VideoModule\Csv;

use Csv\Item;
use Csv\RowHandlerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use SK\VideoModule\Csv\CategoryImportOptions;
use SK\VideoModule\Event\CsvFailedItemEvent;
use SK\VideoModule\Model\Category;

class CategoryRowProcessing implements RowHandlerInterface
{
    private $options;
    private $eventDispatcher;

    /**
     * CategoryRowProcessing constructor
     *
     * @param CategoryImportOptions $options
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(CategoryImportOptions $options, EventDispatcherInterface $eventDispatcher)
    {
        $this->options = $options;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Accepted items handle
     *
     * @param Item $item
     * @return void
     */
    public function success(Item $item): void
    {
        $errors = [];

        try {
            // Если название пустое, значит оно будет идом категории.
            if (!$item->has('title')) {
                $item->put('title', $item->get('category_id'));
            }

            $category = Category::find()
                ->orFilterWhere(['category_id' => $item->get('category_id')])
                ->orFilterWhere(['title' => $item->get('title')])
                ->orFilterWhere(['slug' => $item->get('slug')])
                ->one();

            // Если нашлась категория, но обновлять не нужно - выходим.
            if (null !== $category && false === $this->options->isUpdate) {
                throw new \Exception("Category exists: {$category->title}");
            } elseif (null === $category) {
                $category = new Category;
            }

            if ($item->has('category_id')) {
                $category->category_id = $item->get('category_id');
            }

            if ($item->has('meta_description')) {
                $item->put('meta_description', \mb_substr($item->get('meta_description'), 0, 255));
            }

            if ($item->has('meta_title')) {
                $item->put('meta_title', \mb_substr($item->get('meta_title'), 0, 255));
            }

            $category->setAttributes($item->toArray());

            if (!$category->slug) {
                $slug = $item->get('title');
                $category->generateSlug($slug);
            } elseif ($category->slug && true === $this->options->isReplaceSlug) {
                $slug = $item->get('slug', $item->get('title'));
                $category->generateSlug($slug);
            }

            if ($this->options->isEnable) {
                $category->enable();
            }

            if (!$category->isNewRecord) {
                $category->updated_at = \gmdate('Y-m-d H:i:s');
            }

            if (!$category->save()) {
                $errors = $category->getErrorSummary(true);

                throw new \Exception("Category validation fail: {$category->title}");
            }
        } catch (\Throwable $e) {
            $this->eventDispatcher->dispatch(new CsvFailedItemEvent($item, $e->getMessage(), $errors), CsvFailedItemEvent::NAME_CATEGORY_IMPORT);
        }
    }

    /**
     * Invalid items handle
     *
     * @param Item $item
     * @return void
     */
    public function failure(Item $item): void
    {
        $this->eventDispatcher->dispatch(new CsvFailedItemEvent($item), CsvFailedItemEvent::NAME_CATEGORY_IMPORT);
    }

    /**
     * Exec after hanle all items
     *
     * @return void
     */
    public function eof(): void
    {
    }
}
