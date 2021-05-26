<?php

namespace SK\VideoModule\Category\Import;

/**
 * Class CategoryImportOptions
 *
 * @package SK\VideoModule\Category\Import
 */
class ImportOptions
{
    /**
     * @var bool Category should update, if exists
     */
    public $isUpdate = false;

    /**
     * @var bool Enable category after update\create
     */
    public $isEnable = true;

    /**
     * @var bool Regenerate slug, if category exists
     */
    public $isReplaceSlug = false;
}
