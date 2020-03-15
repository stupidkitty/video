<?php
namespace SK\VideoModule\Event;

use Csv\Item;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class CsvFailedItemEvent
 */
class CsvFailedItemEvent extends Event
{
    public const NAME_CATEGORY_IMPORT = 'video.csv_failed_item.category_import';
    public const NAME_VIDEO_IMPORT = 'video.csv_failed_item.video_import';

    protected $item;
    protected $message;
    protected $details;

    public function __construct(Item $item, string $message = '', array $details = [])
    {
        $this->item = $item;
        $this->message = $message;
        $this->details = $details;
    }

    /**
     * Return failed item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Get the value of message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the value of details
     */
    public function getDetails()
    {
        return $this->details;
    }
}
