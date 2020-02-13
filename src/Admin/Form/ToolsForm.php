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
