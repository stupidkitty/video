<?php

namespace SK\VideoModule\Category\Import\Csv;

use Psr\EventDispatcher\EventDispatcherInterface;
use SK\VideoModule\Category\Import\Event\RowHandleFailedEvent;
use SK\VideoModule\Category\Import\Event\RowHandleSuccessEvent;
use SK\VideoModule\Category\Import\ImportOptions;
use SK\VideoModule\Model\Category;

/**
 * Class CsvRowHandler
 *
 * @package SK\VideoModule\Category\Import\Csv
 */
class CsvRowHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * CsvRowHandler constructor
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param array $row
     * @param ImportOptions $options
     */
    public function handle(array $row, ImportOptions $options): void
    {
        $errors = [];
        $setFields = [];

        try {
            if (!isset($row['category_id']) && !isset($row['title'])) {
                throw new \Exception("One of \"title\" or \"id\" should exists");
            }

            if (isset($row['category_id']) && !\is_numeric($row['category_id'])) {
                throw new \Exception("Category id must be integer: {$row['category_id']}");
            }

            // Если название пустое, значит оно будет идом категории.
            $title = $row['title'] ?? '';

            if ($title === '') {
                $title = (string) $row['category_id'];
            }

            $category = Category::find()
                ->orFilterWhere(['category_id' => isset($row['category_id']) ? (int) $row['category_id'] : null])
                ->orFilterWhere(['title' => $row['title'] ?? null])
                ->orFilterWhere(['slug' => $row['slug'] ?? null])
                ->one();

            // Если нашлась категория, но обновлять не нужно - выходим.
            if (null !== $category && false === $options->isUpdate) {
                throw new \Exception("If you want update the category, you should activate checkbox \"Update if category exists\", title: {$category->title}");
            } elseif (null === $category) {
                $category = new Category();
            }

            if ($category->isNewRecord && isset($row['category_id'])) {
                $category->category_id = (int) $row['category_id'];
            }

            // Названия для категорий обязательны
            if ($category->isNewRecord || isset($row['title'])) {
                $setFields['title'] = \mb_substr(\trim($title), 0, 255);
            }

            if (isset($row['meta_title'])) {
                $setFields['meta_title'] = \mb_substr(\trim($row['meta_title']), 0, 255);
            }

            if (isset($row['meta_description'])) {
                $setFields['meta_description'] = \mb_substr(\trim($row['meta_description']), 0, 255);
            }

            if (isset($row['h1'])) {
                $setFields['h1'] = \mb_substr(\trim($row['h1']), 0, 255);
            }

            if (isset($row['description'])) {
                $setFields['description'] = \trim($row['description']);
            }

            if (isset($row['seotext'])) {
                $setFields['seotext'] = \trim($row['seotext']);
            }

            if (isset($row['param1'])) {
                $setFields['param1'] = \trim($row['param1']);
            }

            if (isset($row['param2'])) {
                $setFields['param2'] = \trim($row['param2']);
            }

            if (isset($row['param3'])) {
                $setFields['param3'] = \trim($row['param3']);
            }

            $category->setAttributes($setFields);

            if (!$category->slug) {
                $category->generateSlug($title);
            } elseif ($category->slug && true === $options->isReplaceSlug) {
                $slug = isset($row['slug']) && (string) $row['slug'] !== '' ? (string) $row['slug'] : $title;
                $category->generateSlug($slug);
            }

            if ($options->isEnable) {
                $category->enable();
            }

            if (!$category->isNewRecord) {
                $category->updated_at = \gmdate('Y-m-d H:i:s');
            }

            if ($category->save()) {
                $this->eventDispatcher->dispatch(new RowHandleSuccessEvent($row, $category), RowHandleSuccessEvent::NAME);
            } else {
                $errors = $category->getErrorSummary(true);
                throw new \Exception("Category save failure: {$category->title}\nErrors:\n" . \implode('\n', $errors));
            }
        } catch (\Throwable $e) {
            $this->eventDispatcher->dispatch(new RowHandleFailedEvent($row, $e->getMessage(), $errors), RowHandleFailedEvent::NAME);
        }
    }
}
