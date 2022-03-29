<?php

namespace SK\VideoModule\Video;

use InvalidArgumentException;
use SK\VideoModule\Model\Video;
use yii\db\Exception;
use yii\helpers\Inflector;

class UpdateVideo
{
    /**
     * @throws Exception
     * @throws \Throwable
     */
    public function update(Video $video, array $data)
    {
        $db = Video::getDb();
        $transaction = $db->beginTransaction();

        try {
            $normalizedData = $this->normalizeKeys($data);

            $videoAttributes = \array_flip($video->attributes());

            $updateVideoFields = \array_intersect_key($normalizedData, $videoAttributes);

            $video->setAttributes($updateVideoFields);

            if (isset($updateVideoFields['slug'])) {
                $video->generateSlug($updateVideoFields['slug']);
            } elseif ((string) $video->slug === '') {
                $video->generateSlug($video->title);
            }

            if ($video->save() === false) {
                $errors = $video->getErrorSummary(true);

                throw new InvalidArgumentException(\implode("\n", $errors));
            }

            if (isset($normalizedData['categories']) && \is_array($normalizedData['categories'])) {
                $categoryIds = \array_column($normalizedData['categories'], 'id');

                $video->updateCategoriesByIds($categoryIds);
            }

            if (isset($normalizedData['poster']) && \is_array($normalizedData['poster'])) {
                if (!$video->hasPoster()) {
                    throw new InvalidArgumentException('Poster not found');
                }

                $posterAttributes = \array_flip($video->poster->attributes());
                $updatePosterFields = \array_intersect_key($normalizedData['poster'], $posterAttributes);

                $video->poster->setAttributes($updatePosterFields);

                if ($video->poster->save() === false) {
                    $errors = $video->getErrorSummary(true);

                    throw new InvalidArgumentException(\implode("\n", $errors));
                }
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }
    }

    private function normalizeKeys(array $data): array
    {
        $out = [];

        foreach ($data as $key => $value) {
            $newKey = Inflector::underscore($key);

            $out[$newKey] = \is_array($value) ? $this->normalizeKeys($value) : $value;
        }

        return $out;
    }
}
