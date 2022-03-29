<?php

namespace SK\VideoModule\Video\UseCase;

use SK\VideoModule\Api\Request\UpdateVideosDto;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Video\UpdateVideo;
use yii\web\NotFoundHttpException;

class BatchUpdateVideos
{
    private UpdateVideo $videoUpdater;

    /**
     * BatchUpdateVideos constructor
     *
     * @param UpdateVideo $videoUpdater
     */
    public function __construct(UpdateVideo $videoUpdater)
    {
        $this->videoUpdater = $videoUpdater;
    }

    /**
     * Update videos from api request.
     *
     * @param UpdateVideosDto $dto
     * @return array|void
     */
    public function update(UpdateVideosDto $dto): array
    {
        if (\count($dto->videos) === 0) {
            return [];
        }

        $handled = [];

        foreach ($dto->videos as $rawVideoData) {
            $videoId = $rawVideoData['id'] ?? 0;

            $handledVideo = [
                'id' => (int) $videoId,
                'errors' => []
            ];

            try {
                $video = Video::findOne(['video_id' => $videoId]);

                if ($video === null) {
                    throw new NotFoundHttpException('Video not found');
                }

                $this->videoUpdater->update($video, $rawVideoData);
            } catch (\InvalidArgumentException $e) {
                $errors = \explode("\n", $e->getMessage());

                $handledVideo['errors'] = $errors;
            } catch (\Throwable $e) {
                $handledVideo['errors'][] = $e->getMessage();
            } finally {
                $handled[] = $handledVideo;
            }
        }

        return $handled;
    }
}
