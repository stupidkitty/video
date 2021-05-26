<?php

namespace SK\VideoModule\Category\Import\Csv;

use SK\VideoModule\Category\Import\CategoryImportOptions;
use SplFileInfo;

/**
 * Class CsvConfig
 *
 * @package SK\VideoModule\Category\Import\Csv
 */
class CsvConfig
{
    /**
     * @var string
     */
    public $enclosure = '"';

    /**
     * @var string
     */
    public $delimiter = ',';

    /**
     * @var string
     */
    public $escape = '\\';

    /**
     * @var SplFileInfo
     */
    public $file;

    /**
     * @var string[]
     */
    public $header = [];

    /**
     * @var bool
     */
    public $skipHeader = false;

    /**
     * @var ?CategoryImportOptions
     */
    public $options = null;
}
