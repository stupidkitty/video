<?php
namespace SK\VideoModule\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class CategoryValidationFailEvent
 */
class CategoryValidationFailEvent extends Event
{
    public const NAME = 'video.category_validation_fail';

    protected $categoryId;
    protected $message;
    protected $details;

    public function __construct(int $categoryId = 0, string $message = '', array $details = [])
    {
        $this->categoryId = $categoryId;
        $this->message = $message;
        $this->details = $details;
    }

    /**
     * Get the value of categoryId
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * Get the value of message
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get the value of details
     */
    public function getDetails(): array
    {
        return $this->details;
    }
}
