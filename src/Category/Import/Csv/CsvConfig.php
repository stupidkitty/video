<?php

namespace SK\VideoModule\Category\Import\Csv;

use SK\VideoModule\Category\Import\ImportOptions;
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
    public string $enclosure = '"';

    /**
     * @var string
     */
    public string $delimiter = ',';

    /**
     * @var string
     */
    public string $escape = '\\';

    /**
     * @var SplFileInfo
     */
    public SplFileInfo $file;

    /**
     * @var string[]
     */
    public array $header = [];

    /**
     * @var bool
     */
    public bool $skipHeader = false;

    /**
     * @var ?ImportOptions
     */
    public ?ImportOptions $options = null;
}
