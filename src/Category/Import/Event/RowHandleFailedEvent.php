<?php

namespace SK\VideoModule\Category\Import\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class RowHandleFailedEvent
 */
class RowHandleFailedEvent extends Event
{
    /**
     * @var string Event name
     */
    public const NAME = 'video.category_import.row_handle_failed';

    /**
     * @var array
     */
    protected $row;

    /**
     * @var string
     */
    protected $message;

    /**
     * @var array
     */
    protected $details;

    /**
     * RowHandleFailedEvent constructor.
     *
     * @param array $row
     * @param string $message
     * @param array $details
     */
    public function __construct(array $row, string $message = '', array $details = [])
    {
        $this->row = $row;
        $this->message = $message;
        $this->details = $details;
    }

    /**
     * Gets the value of row
     *
     * @return array
     */
    public function getRow(): array
    {
        return $this->row;
    }

    /**
     * Gets the value of message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Gets value of details
     *
     * @return array
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
