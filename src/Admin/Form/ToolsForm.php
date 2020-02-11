<?php
namespace SK\VideoModule\Admin\Form;

use Yii;
use yii\base\Model;
use yii\db\Expression;

use ytubes\videos\models\Video;
use ytubes\videos\models\VideoStatus;
use ytubes\videos\models\Category;
use ytubes\videos\models\Image;
use ytubes\videos\models\VideosCategories;
use ytubes\videos\models\VideosRelatedMap;
use ytubes\videos\models\finders\RelatedFinder;
use ytubes\videos\admin\cron\jobs\SetCategoriesThumbs;

/**
 * Модель для обработки формы импорта категорий через цсв файлы или просто текст.
 */
class ToolsForm extends Model
{
	public $delimiter;
    public $enclosure;
    public $fields;

    public $csv_rows;
    public $csv_file;

    public $replace;

    protected $model;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['replace'], 'boolean'],
        ];
    }

    /**
     * Очищает стату по видео полностью.
     * @return boolean
     */
	/*public function clearStats()
	{
			// Очистка статистики тумб
		$q1 = Yii::$app->db->createCommand()
			->update(VideosCategories::tableName(), [
					'is_tested' => 0,
					'tested_at' => null,
					'current_index' => 0,
					'current_shows' => 0,
					'current_clicks' => 0,
					'shows0' => 0,
					'clicks0' => 0,
					'shows1' => 0,
					'clicks1' => 0,
					'shows2' => 0,
					'clicks2' => 0,
					'shows3' => 0,
					'clicks3' => 0,
					'shows4' => 0,
					'clicks4' => 0,
					'shows5' => 0,
					'clicks5' => 0,
					'shows6' => 0,
					'clicks6' => 0,
					'shows7' => 0,
					'clicks7' => 0,
					'shows8' => 0,
					'clicks8' => 0,
					'shows9' => 0,
					'clicks9' => 0,
				])
			->execute();

			// Очитска просмотров, лайков, дизлайков.
		$q2 = Yii::$app->db->createCommand()
			->update(Video::tableName(), [
					'likes' => 0,
					'dislikes' => 0,
					'views' => 0,
				])
			->execute();

			// Очитска просмотров, лайков, дизлайков.
		$q3 = Yii::$app->db->createCommand()
			->update(Category::tableName(), [
					'shows' => 0,
					'clicks' => 0,
					'ctr' => 0,
				])
			->execute();

		$result = $q1 + $q2 + $q3;

		return $result;
	}*/

    /**
     * Задает случайную дату в промежутке от текущего времени до года назад
     * @return boolean
     */
	/*public function randomDate()
	{
			// Рандом для видео в таблице `videos`
		$q1 = Yii::$app->db->createCommand()
			->update(Video::tableName(), [
					'published_at' => new Expression('FROM_UNIXTIME(UNIX_TIMESTAMP(NOW()) - FLOOR(0 + (RAND() * 31536000)))'),
				])
			->execute();

			// Рандом в таблице videos_stats
		$sql = 'UPDATE `' . VideosCategories::tableName() . '` as `vs`
				LEFT JOIN (
					SELECT `video_id`, `published_at` FROM `' . Video::tableName() . '`
				) as `v`
				ON `v`.`video_id`=`vs`.`video_id`
			SET `vs`.`published_at`=`v`.`published_at`';

		$q2 = Yii::$app->db->createCommand($sql)
			->execute();

		$result = $q1 + $q2;

		return $result;
	}*/

    /**
     * Удаляет видео, тумбы, стату по ним. // Сделать через транзакции, добавить исключения
     * @return boolean
     */
	public function clearVideos()
	{
			// Очищает таблицу категорий
		Yii::$app->db->createCommand()
			->truncateTable(VideosCategories::tableName())
			->execute();

			// Удаляем фотки
		Yii::$app->db->createCommand()
			->delete(Image::tableName(), '1=1')
			->execute();

			// Удаляем видео
		Yii::$app->db->createCommand()
			->delete(Video::tableName(), '1=1')
			->execute();

			// Очищаем релатеды.
		Yii::$app->db->createCommand()
			->truncateTable(VideosRelatedMap::tableName())
			->execute();

			// Сброс автоинкремента у видео
		$sql = 'ALTER TABLE `' . Video::tableName() . '` AUTO_INCREMENT=1';
		Yii::$app->db->createCommand($sql)
			->execute();

			// Сброс автоинкремента у скриншотов
		$sql = 'ALTER TABLE `' . Image::tableName() . '` AUTO_INCREMENT=1';
		Yii::$app->db->createCommand($sql)
			->execute();

		return true;
	}
    /**
     * Удаляет все related из базы
     * @return boolean
     */
	/*public function clearRelated()
	{
		try {
			Yii::$app->db->createCommand()
				->truncateTable(VideosRelatedMap::tableName())
				->execute();

			$result = true;
		} catch(\Exception $e) {
			$result = false;
		}

		return $result;
	}*/
    /**
     * Удаляет все related из базы
     *
     * @return boolean
     */
	/*public function recalculateCategoriesVideos()
	{
		$sql = "
			UPDATE `videos_categories` as `vc`
			LEFT JOIN (
				SELECT `category_id`, COUNT(DISTINCT `video_id`) as `videos_num` FROM `videos_stats` GROUP BY `category_id`
			) as `vs` ON `vs`.`category_id`=`vc`.`category_id`
			SET `vc`.`videos_num` = `vs`.`videos_num`
		";

		$result = Yii::$app->db->createCommand($sql)
			->execute();

		return $result;
	}*/

    /**
     * Устанавливает тумбы у категорий
     *
     * @return boolean
     */
	/*public function setCategoriesThumbs()
	{
		$job = new SetCategoriesThumbs();
		$job->handle();

		return true;
	}*/
	/**
	* Перегенерация релатедов у всех видео.
	*
	*/
	public function regenerateRelated()
	{
		$videos = Video::find()
			->select(['video_id'])
			->where(['status' => VideoStatus::PUBLISH])
			->asArray()
			->all();
		if (!empty($videos)) {
			$relatedFinder = new RelatedFinder();

			foreach ($videos as $video) {
				$relatedFinder->findAndSaveRelatedIds((int) $video['video_id']);
			}
		}

		return true;
	}
}
