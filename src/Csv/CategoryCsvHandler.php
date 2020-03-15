<?php
namespace SK\VideoModule\Csv;

use Csv\Parser;
use Csv\Validator;
use Psr\EventDispatcher\EventDispatcherInterface;
use SK\VideoModule\Csv\CategoryCsvDto;
use SK\VideoModule\Event\CsvFailedItemEvent;

class CategoryCsvHandler
{
    private $eventDispatcher;
    protected $failedItems;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->failedItems = new FailedItemsCollection;

        $this->eventDispatcher->addListener(CsvFailedItemEvent::NAME_CATEGORY_IMPORT, function (CsvFailedItemEvent $event) {
            $this->failedItems->put(null, [
                'item' => $event->getItem(),
                'message' => $event->getMessage(),
                'details' => $event->getDetails(),
            ]);
        });
    }

    public function handle(CategoryCsvDto $dto)
    {
        $file = $dto->file->openFile();
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($dto->delimiter, $dto->enclosure);

        $parser = new Parser($file, [
            'hasHeader' => $dto->isSkipFirstLine === true,
            'skipFirstLine' => $dto->isSkipFirstLine,
            'stopWhenError' => false,
            'ignoreKeys' => ['skip'],
        ]);
        $parser->setValidator(new Validator([
            'category_id' => 'integer|required_without:title',
            'title' => 'string|required_without:category_id',
            'skip' => 'string',
            'slug' => 'string',
            'meta_title' => 'string',
            'meta_description' => 'string',
            'h1' => 'string',
            'description' => 'string',
            'seotext' => 'string',
            'param1' => 'string',
            'param2' => 'string',
            'param3' => 'string',
        ]));
        $parser->setRowHandler(new CategoryRowProcessing($dto->options, $this->eventDispatcher));
        $parser->setHeader($dto->fields);
        $parser->run();
    }

    /**
     * Get the value of failedItems
     *
     * @return array
     */
    public function getFailedItems(): FailedItemsCollection
    {
        return $this->failedItems;
    }
}
