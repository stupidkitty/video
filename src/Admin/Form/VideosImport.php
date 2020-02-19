<?php
namespace SK\VideoModule\Admin\Form;

use Yii;
use LimitIterator;
use SplFileObject;
use yii\base\Model;
use yii\db\Expression;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use SK\VideoModule\Model\Image;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\Category;
use SK\VideoModule\Model\ImportFeed;
use RS\Component\Core\Settings\SettingsInterface;
use RS\Component\Core\Generator\TimeIntervalGenerator;

/**
 * Пометка: Сделать проверку на соответствие полей. Если не соответствует - писать в лог.
 */

/**
 * Модель для обработки формы импорта категорий через цсв файлы или просто текст.
 */
class VideosImport extends Model
{
    public $delimiter;
    public $enclosure;
    public $fields;

    public $csv_rows;
    public $csv_file;

    /**
     * @var string $default_date Дефолтная дата поста (текущая)
     */
    public $default_date;
    /**
     * @var string $set_published_at_method Метод заполнения времени постинга.
     */
    public $set_published_at_method;
    /**
     * @var int $user_id Автор добавленных постов.
     */
    public $user_id;
    /**
     * @var int $status Статус новой записи.
     */
    public $status;
    /**
     * @var string $template Шаблон вывода вставленного видео.
     */
    public $template;
    /**
     * @var boolean $skip_new_categories Пропускать создание новых видео, если исходный урл уже есть.
     */
    public $skip_duplicate_urls;
    /**
     * @var boolean $skip_new_categories Пропускать создание новых видео, если emebd код такой уже есть.
     */
    public $skip_duplicate_embeds;
    /**
     * @var boolean $skip_new_categories Не создавать новые категории.
     */
    public $skip_new_categories;
    /**
     * @var boolean $external_images Будут использоваться внешние тумбы или скачиваться и нарезаться на сервере.
     */
    public $external_images;
    /**
     * @var boolean Пропуск первой строчки в CSV.
     */
    public $skip_first_line;
    /**
     * @var int $imported_rows_num Количество вставленных записей.
     */
    public $imported_rows_num = 0;
    /**
     * @var array $categories Категории раздела видео.
     */
    protected $categories;
    /**
     * @var array $option Опции для тега select, отвечающего за набор полей csv.
     */
    protected $options = [];
    /**
     * @var array $not_inserted_rows Забракованные строчки из CSV.
     */
    protected $not_inserted_rows = [];
    /**
     * @var array $not_inserted_ids Не вставленные иды видео, если такие были.
     */
    protected $not_inserted_ids = [];
    /**
     * @var \DateTime $startDate
     */
    protected $startDate;

    protected $timeIntervalGenerator;

    public function __construct(ImportFeed $importFeed, $config = [])
    {
        parent::__construct($config);

        set_time_limit(0);

        $this->attributes = $importFeed->getAttributes();
        $this->options = $importFeed->getFieldsOptions();

        $this->default_date = (new \DateTime('now'))->format('Y-m-d H:i:s');
        $this->set_published_at_method = 'auto_add';

        // Отключить логи
        if (Yii::$app->hasModule('log') && is_object(Yii::$app->log->targets['file'])) {
            Yii::$app->log->targets['file']->enabled = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['delimiter', 'fields'], 'required'],
            ['fields', 'each', 'rule' => ['string'], 'skipOnEmpty' => false],
            [['delimiter', 'enclosure', 'csv_rows'], 'string'],
            [['delimiter', 'enclosure', 'csv_rows', 'template'], 'filter', 'filter' => 'trim'],
            [['skip_duplicate_urls', 'skip_duplicate_embeds', 'skip_new_categories', 'external_images', 'skip_first_line'], 'boolean'],
            [['skip_duplicate_urls', 'skip_duplicate_embeds', 'skip_new_categories', 'external_images', 'skip_first_line'], 'filter', 'filter' => function ($value) {
                return (bool) $value;
            }],
            [['default_date', 'set_published_at_method'], 'string'],
            [['status', 'user_id'], 'integer'],
            [['template'], 'string', 'max' => 64],

            [['csv_file'], 'file', 'checkExtensionByMimeType' => false, 'skipOnEmpty' => true, 'extensions' => 'csv', 'maxFiles' => 1, 'mimeTypes' => 'text/plain'],
        ];
    }

    /**
     * Проверяет правильность данных в файле или текстовом поле. Затем сохраняет в базу.
     *
     * @return boolean whether the model passes validation
     */
    public function save()
    {
        // Стартовая дата для автопостинга
        if ($this->set_published_at_method === 'auto_add') {
            $last_published_at = Video::find()
                ->where(['status' => Video::STATUS_ACTIVE])
                ->asArray()
                ->max('published_at');

            $lastPublishedAt = new \DateTime($last_published_at);

            $lastDay = new \DateTime('now -1 day');

            if ($lastDay > $lastPublishedAt) { // если постов небыло больше суток, то стартуем с текущей даты
                $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', $this->default_date);
            } else {
                $startDate = clone $lastPublishedAt;
            }
        } else {
            $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', $this->default_date);
        }

        $settings = Yii::$container->get(SettingsInterface::class);
        $autopostingInterval = (int) $settings->get('autoposting_fixed_interval', 8640, 'videos');
        $autopostingDispersion = (int) $settings->get('autoposting_spread_interval', 600, 'videos');

        $dateInterval = \DateInterval::createFromDateString("{$autopostingInterval} seconds");
        $this->timeIntervalGenerator = new TimeIntervalGenerator($startDate, $dateInterval, $autopostingDispersion);

            // Если категории заданы по ид, то у них приоритет и добавляться категории будут через иды.
        if (in_array('categories_ids', $this->fields)) {
            $this->categories = Category::find()
                ->indexBy('category_id')
                ->all();
        } else {
            $this->categories = Category::find()
                ->indexBy('title')
                ->all();
        }

        $filepath = Yii::getAlias("@runtime/tmp/videos_import.csv");

        if (!is_dir(dirname($filepath))) {
            FileHelper::CreateDirectory(dirname($filepath), 0755);
        }

        // Если загружен файл, читаем с него.
        if (null !== $this->csv_file) {

            $this->csv_file->saveAs($filepath);

            @unlink($this->csv_file->tempName);

        // Если файла нет, но загружено через текстовое поле, то будем читать с него.
        } elseif (!empty($this->csv_rows)) {
            file_put_contents($filepath, $this->csv_rows);
        }

        if (!is_file($filepath)) {
            $this->addError('csv_rows', 'Cannot create temporary file.');

            return false;
        }

        $file = new SplFileObject($filepath);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($this->delimiter, $this->enclosure);

        $this->parseCsvFromFile($file);

        if (is_file($filepath)) {
            unlink($filepath);
        }
    }

    /**
     * Разбор CSV из файла.
     */
    protected function parseCsvFromFile(SplFileObject $file)
    {
        $fieldsNum = count($this->fields);

        $startLine = 0;
        if (true === $this->skip_first_line) {
            $startLine = 1;
        }

        $iterator = new LimitIterator($file, $startLine);

        foreach ($iterator as $lineNumber => $csvParsedString) {
            // Совпадает ли количество заданных полей с количеством элементов в CSV строке
            $elementsNum = count($csvParsedString);
            if ($fieldsNum !== $elementsNum) {
                $row = $this->str_putcsv($csvParsedString, $this->delimiter, $this->enclosure);
                $this->addError('csv_rows', "Строка <b class=\"text-dark-gray\">{$row}</b> не соответствует конфигурации колонок. Количество полей указано: {$fieldsNum}, фактическое количество колонок: {$elementsNum}");
                continue;
            }

            $newItem = [];
            foreach ($this->fields as $key => $field) {
                if (isset($csvParsedString[$key]) && $field !== 'skip') {
                    $newItem[$field] = trim($csvParsedString[$key]);
                }
            }

            if (empty($newItem)) {
                continue;
            }

            if ($this->exists($newItem)) {
                $this->addError('csv_rows', "Строка номер <b class=\"text-dark-gray\">{$lineNumber}</b> дубликат");
                $this->not_inserted_rows[] = $this->str_putcsv($csvParsedString, $this->delimiter, $this->enclosure);

                if (isset($newItem['video_id'])) {
                    $this->not_inserted_ids[] = (int) $newItem['video_id'];
                }

                continue;
            }

            if (true === $this->insertItem($newItem)) {
                $this->imported_rows_num ++;

                // если дата задана интервалом, сгенерируем следующую временную метку
                if ('auto_add' === $this->set_published_at_method) {
                    $this->timeIntervalGenerator->next();
                }
            } else {
                $this->not_inserted_rows[] = $this->str_putcsv($csvParsedString, $this->delimiter, $this->enclosure);
            }
        }
    }

    /**
     * Проверяет, существует ли видео в базе по нескольким параметрам.
     *
     * @param array $row Массив с данными вставляемого видео.
     *
     * @return boolean
     */
    protected function exists($row)
    {
        $existsVideoQuery = Video::find()
            ->select(new Expression('1'))
            ->limit(1);

            // Ищем, существует ли видео по иду.
        if (isset($row['video_id'])) {
            $existsVideoQuery->filterWhere(['video_id' => (int) $row['video_id']]);
        }

        // Ищем, существует ли видео по embed коду.
        if (true === $this->skip_duplicate_embeds && isset($row['embed'])) {
            $existsVideoQuery->orFilterWhere(['embed' => $row['embed']]);
        }

        // Если у запроса есть проверки, то сделаем запрос.
        if (!empty($existsVideoQuery->where)) {
            return $existsVideoQuery->exists();
        }

        return false;
    }

    /**
     * Осуществляет вставку видео. Если видео уже существут в базе (проверяется по embed), то вставка просто игнорируется.
     *
     * @param array $newVideo массив с данными для вставки нового видео.
     *
     * @return boolean была ли произведена вставка
     */
    protected function insertItem($newVideo)
    {
        $video = new Video();
        $currentTime = gmdate('Y:m:d H:i:s');

            // Если у видео есть категории, вынесем их в отдельный массив.
        $videoCategories = [];
        if (!empty($newVideo['categories_ids'])) {
            $videoCategories = explode(',', $newVideo['categories_ids']);
            unset($newVideo['categories_ids']);

            // Или категории по названиям.
        } elseif (!empty($newVideo['categories'])) {
            $videoCategories = explode(',', $newVideo['categories']);
            unset($newVideo['categories']);
        }

            // Если у видео есть скриншоты, вынесем их в отдельный массив.
        $videoScreenshots = [];
        if (!empty($newVideo['images'])) {
            $videoScreenshots = explode(',', $newVideo['images']);
            unset($newVideo['images']);
        }

        // Запись остальных атрибутов
        $video->setAttributes($newVideo);

        if (empty($newVideo['title'])) {
            $video->title = 'default-' . microtime();
        } else {
            $video->title = StringHelper::truncate($newVideo['title'], 255, '');
        }

        $slug = empty($newVideo['slug']) ? $video->title : $newVideo['slug'];
        $slug = StringHelper::truncate($slug, 249, '');
        $video->generateSlug($slug);

        // Шаблон для ролика
        if (!empty($this->template)) {
            $video->template = $this->template;
        }

        // Время публикации поста, временный вариант.
        if (isset($newVideo['published_at'])) {
            $video->published_at = $newVideo['published_at'];
        } else {
            $video->published_at = $this->getPublishedAt();
        }

        // Владелец поста
        $video->user_id = $this->user_id;
        // Статус
        $video->status = $this->status;

        $video->updated_at = $currentTime;
        $video->created_at = $currentTime;

        if (false === $video->save(true)) {
            $validateErrors = [];
            $validateErrors[$video->title] = $video->getErrorSummary(true);
            $this->addError('csv_rows', $validateErrors);

            if (isset($newVideo['video_id'])) {
                $this->not_inserted_ids[] = $newVideo['video_id'];
            }

            return false;
        }

        foreach ($videoCategories as $videoCategory) {
            $categoryTitle = strip_tags($videoCategory);
            // Если категории не существует и флажок "не создавать новые" выключен, добавим категорию.
            if (empty($this->categories[$categoryTitle]) && false === $this->skip_new_categories) {
                $category = new Category();

                $category->title = $categoryTitle;
                $category->generateSlug($categoryTitle);
                $category->meta_title = $categoryTitle;
                $category->h1 = $categoryTitle;
                $category->updated_at = $currentTime;
                $category->created_at = $currentTime;
                $category->save();

                $this->categories[$categoryTitle] = $category;
            }

            if (isset($this->categories[$categoryTitle])) {
                $video->addCategory($this->categories[$categoryTitle]);
            }
        }


        foreach ($videoScreenshots as $key => $videoScreenshot) {
            $screenshot = new Image();

            $screenshot->video_id = $video->video_id;
            $screenshot->position = $key;
            $screenshot->source_url = trim($videoScreenshot);
            $screenshot->created_at = $currentTime;

            if (true === $this->external_images) {
                $screenshot->status = 10;
                $screenshot->filepath = trim($videoScreenshot);
            } else {
                $screenshot->status = 0;
            }

            if ($screenshot->save(true)) {
                //$screenshots[] = $screenshot;

                $video->addImage($screenshot);

                if ($key === 0) {
                    $video->setPoster($screenshot);
                }
            }
        }

        return true;
    }

    /**
     * Собирает CSV строчку из массива.
     *
     * @param array $input
     *
     * @param string $delimiter
     *
     * @param string $enclosure
     *
     * @return string
     */
    protected function str_putcsv($input, $delimiter = ',', $enclosure = '"')
    {
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, $input, $delimiter, $enclosure);
        rewind($fp);
        $data = fread($fp, 1048576);
        fclose($fp);
        return rtrim($data, "\n");
    }

    /**
     * @inheritdoc
     */
    protected function getPublishedAt()
    {
        return $this->timeIntervalGenerator
            ->current()
            ->format('Y-m-d H:i:s');
    }

    /**
     * @inheritdoc
     */
    public function hasNotInsertedRows() {
        return !empty($this->not_inserted_rows);
    }

    /**
     * @inheritdoc
     */
    public function getNotInsertedRows()
    {
        return $this->not_inserted_rows;
    }

    /**
     * @inheritdoc
     */
    public function hasNotInsertedIds() {
        return !empty($this->not_inserted_ids);
    }

    /**
     * @inheritdoc
     */
    public function getNotInsertedIds()
    {
        return $this->not_inserted_ids;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @inheritdoc
     */
    public function getImportedRowsNum()
    {
        return $this->imported_rows_num;
    }
}
