<?php
namespace SK\VideoModule\Csv;

use Csv\Item;
use Csv\RowHandlerInterface;
use SK\VideoModule\Csv\CategoryImportOptions;

class CategoryRowProcessing implements RowHandlerInterface
{
    private $options;
    private $fields;

    public function __construct(array $fields, CategoryImportOptions $options)
    {
        $this->fields = $fields;
        $this->options = $options;
      }

    public function success(Item $row): void
    {
        try {
            //echo 'Success:' . $row->get(0);
            //$data = $this->prepareRow($row);
            dump($row);
        } catch (\Throwable $e) {
            \Yii::warning($e->getMessage());
        }
    }

    public function failure(Item $row): void
    {
        echo 'fail:';
        dump($row);
    }

    public function eof(): void
    {
    }

    /*private function prepareRow($row)
    {
        $data = [];
        $arrayValues = array_values($row['items']);
        foreach ($this->fields as $key => $field) {
            $data[$field] = $row->get($key);
        }
        dump($row, $data, $this->fields, $arrayValues); exit;
        return $data;
    }*/
}
