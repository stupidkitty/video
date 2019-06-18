<?php
namespace SK\VideoModule\Model;

use URLify;

trait SlugGeneratorTrait
{
    /**
     * Генерирует slug исходя из title. Также присоединяет численный суффикс, если слаг не уникален.
     *
     * @param string $title
     *
     * @return void
     */
    public function generateSlug($title = '')
    {
        if ('' === $title) {
            $title = $this->getTitle();
        }

        $slug = URLify::filter($title, 240);

        if (!$slug) {
            $slug = 'default-slug';
        }

        // если слаг существует, добавляем к нему индекс, до тех пор пока не станет уникальным.
        if ($this->isAttributeChanged('slug') && $this->existsSlug($slug)) {
            for ($index = 1; $this->existsSlug($new_slug = $slug . '-' . $index); $index ++) {}

            $slug = $new_slug;
        }

        $this->setSlug($slug);
    }

    /**
     * Проверяет существует ли слаг у какой либо записи в базе.
     * Не учитывает свой собственный слаг
     *
     * @param string $slug
     *
     * @return bool
     */
    private function existsSlug($slug)
    {
        return static::find()
            ->where(['slug' => $slug])
            ->andFilterWhere(['!=', $this->primaryKey()[0], $this->getId()])
            ->exists();
    }
}
