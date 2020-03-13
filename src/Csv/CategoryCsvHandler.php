<?php
namespace SK\VideoModule\Csv;

use Csv\Parser;
use Csv\Validator;
use SK\VideoModule\Csv\CategoryCsvDto;

class CategoryCsvHandler
{
    public function handle(CategoryCsvDto $dto)
    {
        try {
            $file = $dto->file->openFile();
            $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
            $file->setCsvControl($dto->delimiter, $dto->enclosure);

            $parser = new Parser($file, [
                'hasHeader' => $dto->isSkipFirstLine === true,
                'skipFirstLine' => $dto->isSkipFirstLine,
                'stopWhenError' => false,
                //'ignoreKeys' => ['skip'], сделать игнор ключей
            ]);
            $parser->setValidator(new Validator([
                'category_id' => 'integer',
                'title' => 'string',
                'skip' => 'string',
                'slug' => 'string',
                'meta_title' => 'string',
                'meta_description' => 'string',
                'h1' => 'string',
                'description' => 'string',
                'seotext' => 'string',
                'param1' => 'string',
                'param2' => 'string',
                'param3' => 'string'
            ]));
            $parser->setRowHandler(new CategoryRowProcessing($dto->fields, $dto->options));
            $parser->setHeader($dto->fields);
            $parser->run();
        } catch (\Throwable $e) {
            //$this->logger->error($e->getMessage());
            echo $e->getMessage();
        }
    }

    /**
     * Скачивание цсв файла фаппи.
     *
     * @return \SplFileObject
     */
    public function downloadCsv(): \SplFileObject
    {
        return $this->csvDownloader->download();
    }
}
