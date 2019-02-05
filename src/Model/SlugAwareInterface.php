<?php
namespace SK\VideoModule\Model;

interface SlugAwareInterface
{
    /**
     * @return string
     */
    public function getTitle(); // отложено до новой версии пыха

    /**
     * @param string $title
     */
    public function setTitle($title); // отложено до новой версии пыха

    /**
     * @return string|null
     */
    public function getSlug();

    /**
     * @param string|null $slug
     */
    public function setSlug($slug);
}
