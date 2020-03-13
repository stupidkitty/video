<?php
namespace SK\VideoModule\Csv;

use Offdev\Csv\Item;
use Offdev\Csv\ProcessorInterface;
use SK\VideoModule\Csv\CategoryImportOptions;

class CategoryRowProcessing implements ProcessorInterface
{
    private $options;
    private $fields;

    public function __construct(array $fields, CategoryImportOptions $options)
    {
        $this->fields = $fields;
        $this->options = $options;
      }

    public function processRecord(Item $row): void
    {
        try {
            //echo 'Success:' . $row->get(0);
            $data = $this->prepareRow($row);
            dump($data);
        } catch (\Throwable $e) {
            \Yii::warning($e->getMessage());
        }
    }

    public function processInvalidRecord(Item $row): void
    {
        dump($row);
    }

    public function eof(): void
    {
        echo "---EOF---" . PHP_EOL;
    }

    private function prepareRow($row)
    {
        $data = [];
        $arrayValues = array_values($row['items']);
        foreach ($this->fields as $key => $field) {
            $data[$field] = $row->get($key);
        }
        dump($row, $data, $this->fields, $arrayValues); exit;
        return $data;
    }
}
