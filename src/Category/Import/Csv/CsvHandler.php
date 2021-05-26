<?php

namespace SK\VideoModule\Category\Import\Csv;

use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;

/**
 * Class CsvHandler
 *
 * @package SK\VideoModule\Category\Import\Csv
 */
class CsvHandler
{
    /**
     * @var CsvRowHandler
     */
    private $rowHandler;

    /**
     * CsvHandler constructor.
     *
     * @param CsvRowHandler $rowHandler
     */
    public function __construct(CsvRowHandler $rowHandler)
    {
        $this->rowHandler = $rowHandler;
    }

    /**
     * @throws InvalidArgument
     * @throws Exception
     */
    public function handle(CsvConfig $config)
    {
        $reader = Reader::createFromFileObject($config->file->openFile());
        $reader->setDelimiter($config->delimiter);
        $reader->setEnclosure($config->enclosure);
        $reader->setEscape($config->escape);
        $reader->skipEmptyRecords();

        if ($config->skipHeader === true) {
            $reader->setHeaderOffset(0);
        }

        foreach ($reader->getRecords($config->header) as $record) {
            $this->rowHandler->handle($record, $config->options);
        }
    }
}
