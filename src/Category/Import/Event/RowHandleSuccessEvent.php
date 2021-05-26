<?php

namespace SK\VideoModule\Category\Import\Event;

use SK\VideoModule\Model\Category;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class RowHandleSuccessEvent
 */
class RowHandleSuccessEvent extends Event
{
    /**
     * @var string Event name
     */
    public const NAME = 'video.category_import.row_handle_success';

    /**
     * @var array
     */
    protected $row;

    /**
     * @var Category
     */
    protected $category;

    /**
     * RowHandleSuccessEvent constructor.
     *
     * @param array $row
     * @param Category $category
     */
    public function __construct(array $row, Category $category)
    {
        $this->row = $row;
        $this->category = $category;
    }

    /**
     * Gets the value of row
     *
     * @return array
     */
    public function getRow()
    {
        return $this->row;
    }

    /**
     * Gets the value of category
     *
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->message;
    }
}
