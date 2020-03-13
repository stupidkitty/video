<?php
namespace SK\VideoModule\Admin\Form;

use Yii;
use LimitIterator;
use SplFileObject;
use yii\base\Model;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;
use SK\VideoModule\Model\Category;
use SK\VideoModule\Csv\CategoryCsvDto;
use SK\VideoModule\Csv\CategoryImportOptions;

/**
 * Модель для обработки формы импорта категорий через цсв файлы или просто текст.
 */
class CategoriesImportForm extends Model
{
    public $delimiter;
    public $enclosure;
    public $fields;

    public $csv_file;

    public $isUpdate;
    public $isEnable;
    public $isSkipFirstLine;
    public $isReplaceSlug;

    protected $not_inserted_rows = [];

    /**
     * @var int $imported_rows_num количество вставленных записей.
     */
    protected $imported_rows_num = 0;

    protected $options = [
        'skip' => 'Пропустить',
        'category_id' => 'id',
        'title' => 'Название',
        'slug' => 'Слаг',
        'meta_title' => 'Мета заголовок',
        'meta_description' => 'Мета описание',
        'h1' => 'Заголовок H1',
        'description' => 'Описание',
        'seotext' => 'СЕО текст',
        'param1' => 'Доп. поле 1',
        'param2' => 'Доп. поле 2',
        'param3' => 'Доп. поле 3',
    ];

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->delimiter = '|';
        $this->enclosure = '"';
        $this->fields = ['skip'];
        $this->isUpdate = false;
        $this->isEnable = true;
        $this->isSkipFirstLine = true;
        $this->isReplaceSlug = false;

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
            ['fields', 'each', 'rule' => ['in', 'range' => \array_keys($this->options)], 'skipOnEmpty' => false],

            [['delimiter', 'enclosure'], 'string'],
            [['delimiter', 'enclosure'], 'trim'],

            [['isUpdate', 'isSkipFirstLine', 'isEnable', 'isReplaceSlug'], 'boolean'],

            [['csv_file'], 'file', 'checkExtensionByMimeType' => false, 'skipOnEmpty' => false, 'extensions' => 'csv', 'maxFiles' => 1, 'mimeTypes' => 'text/plain'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        $this->csv_file = UploadedFile::getInstance($this, 'csv_file');

        return $this->validate();
    }

    public function getData()
    {
        $dto = new CategoryCsvDto;

        $dto->delimiter = $this->delimiter;
        $dto->enclosure = $this->enclosure;
        $dto->fields = $this->fields;
        $dto->file = new \SplFileInfo($this->csv_file->tempName);
        $dto->isSkipFirstLine = (bool) $this->isSkipFirstLine;

        $dto->options = new CategoryImportOptions;
        $dto->options->isUpdate = (bool) $this->isUpdate;
        $dto->options->isEnable = (bool) $this->isEnable;
        $dto->options->isReplaceSlug = (bool) $this->isReplaceSlug;

        return $dto;
    }

    /**
     * Проверяет правильность данных в файле или текстовом поле. Затем сохраняет в базу.
     * @return boolean whether the model passes validation
     */
    public function save()
    {
        $filepath = Yii::getAlias("@runtime/tmp/categories_import.csv");

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
            $this->addError('csv_rows', 'Temporary file not exists');

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

            if (true === $this->insertItem($newItem)) {
                $this->imported_rows_num++;
            } else {
                $this->not_inserted_rows[] = $this->str_putcsv($csvParsedString, $this->delimiter, $this->enclosure);
            }
        }
    }

    /**
     * Осуществляет вставку категории. Если таковая уже существует (чек по тайтлу и иду) то проверяется флажок, перезаписывать или нет.
     * В случае перезаписи назначает новые параметры исходя из данных файла.
     *
     * @param array $newCategory Массив с данным для вставки новой категории
     *
     * @return boolean было ли произведено обновление или вставка
     */
    protected function insertItem($newCategory)
    {
        $currentTime = gmdate('Y-m-d H:i:s');

        // Ищем, существует ли категория.
        if (!empty($newCategory['category_id'])) {
            $category = Category::findOne($newCategory['category_id']);
        } elseif (!empty($newCategory['title'])) {
            $category = Category::findOne(['title' => $newCategory['title']]);
        } else {
            $this->addError('csv_rows', 'Требуется название или ID');
            return false;
        }

        // Если название все таки пустое, значит оно будет идом категории.
        if (empty($newCategory['title'])) {
            $newCategory['title'] = $newCategory['category_id'];
        }

        // Если ничего не нашлось, будем вставлять новый.
        if (null === $category) {
            $category = new Category();
        } else {
            // Если переписывать не нужно существующую категорию, то просто проигнорировать ее.
            if (false === $this->update_category) {
                $this->addError('csv_rows', "<b>{$category->title}</b> дубликат");
                return false;
            }
        }

        if (isset($newCategory['category_id'])) {
            $category->category_id = (int) $newCategory['category_id'];
        }

        if (isset($newCategory['meta_description'])) {
            $newCategory['meta_description'] = StringHelper::truncate($newCategory['meta_description'], 255, false);
        }

        if (isset($newCategory['meta_title'])) {
            $newCategory['meta_title'] = StringHelper::truncate($newCategory['meta_title'], 255, false);
        }

        $category->setAttributes($newCategory);

        $slug = empty($newCategory['slug']) ? $newCategory['title'] : $newCategory['slug'];
        $category->generateSlug($slug);

        if (true === $this->enable) {
            $category->enable();
        }

        if ($category->isNewRecord) {
            $category->updated_at = $currentTime;
            $category->created_at = $currentTime;
        } else {
            $category->updated_at = $currentTime;
        }

        if (!$category->save(true)) {
            $validateErrors = [];
            $validateErrors[$category->title] = $category->getErrorSummary(true);
            $this->addError('csv_rows', $validateErrors);

            return false;
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
    public function hasNotInsertedRows()
    {
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
