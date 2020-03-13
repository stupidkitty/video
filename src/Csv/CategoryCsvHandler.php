<?php
namespace SK\VideoModule\Csv;

use Offdev\Csv\Parser;
use Offdev\Csv\Stream;
use Offdev\Csv\Validator;
use SK\VideoModule\Csv\CategoryCsvDto;

class CategoryCsvHandler
{
    public function handle(CategoryCsvDto $dto)
    {
        try {
            $stream = Stream::factory(\fopen($dto->file, 'r'));
            $parser = new Parser($stream, [
                Parser::OPTION_BUFSIZE => 1024 * 20,
                Parser::OPTION_HEADER => $dto->options->isSkipFirstLine === true,
                Parser::OPTION_DELIMITER => $dto->delimiter,
                Parser::OPTION_STRING_ENCLOSURE => $dto->enclosure,
                Parser::OPTION_ESCAPE_CHAR => '\\',
                Parser::OPTION_THROWS => false,
            ]);
            /*$parser->setValidator(new Validator([
                'required|integer', // fappy id
                'regex:/^$/i', // top id
                'required|string', // title
                'required|integer', // video width
                'required|integer', // video height
                'required|integer', // duration
                'required|string|in:HD,SD', // quality
                'date_format:Y-m-d H:i:s|nullable', // post_date
                'string|nullable', // categories
                'string|nullable', // tags
                'string|nullable', // models
                'required|string', // video link
                'required|string', // url on fappy
                'required|string', // screenshots_prefix
                'required|string', // main_screenshot
                'required|string', // screenshots
            ]));*/
            $parser->setProcessor(new CategoryRowProcessing($dto->fields, $dto->options));
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
