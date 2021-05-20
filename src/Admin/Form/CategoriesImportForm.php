<?php
namespace SK\VideoModule\Admin\Form;

use SK\VideoModule\Csv\CategoryCsvDto;
use SK\VideoModule\Csv\CategoryImportOptions;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

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

    /**
     * Options for selection of csv fields
     *
     * @var string[]
     */
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
     * @return string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

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
        if (Yii::$app->hasModule('log') && \is_object(Yii::$app->log->targets['file'])) {
            Yii::$app->log->targets['file']->enabled = false;
        }
    }

    /**
     * @inheritdoc
     */
    public function formName(): string
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
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
     * Validate form
     *
     * @return bool
     */
    public function isValid(): bool
    {
        $this->csv_file = UploadedFile::getInstance($this, 'csv_file');

        return $this->validate();
    }

    /**
     * Get user options for csv handler
     *
     * @return CategoryCsvDto
     */
    public function getData(): CategoryCsvDto
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
}
