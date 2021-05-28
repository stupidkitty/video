<?php

namespace SK\VideoModule\Admin\Form;

use SK\VideoModule\Category\Import\ImportOptions;
use SK\VideoModule\Category\Import\Csv\CsvConfig;
use SK\VideoModule\Model\CategoryImportFeed;
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
    public $csv_text;

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
    public function __construct(CategoryImportFeed $feed, $config = [])
    {
        parent::__construct($config);

        $this->delimiter = $feed->delimiter;
        $this->enclosure = $feed->enclosure;
        $this->fields = $feed->fields;
        $this->csv_text = '';
        $this->isSkipFirstLine = $feed->skip_first_line;
        $this->isUpdate = $feed->update_exists;
        $this->isEnable = $feed->activate;
        $this->isReplaceSlug = $feed->update_slug;

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

            [['delimiter', 'enclosure', 'csv_text'], 'string'],
            [['delimiter', 'enclosure'], 'trim'],

            [['isUpdate', 'isSkipFirstLine', 'isEnable', 'isReplaceSlug'], 'boolean'],

            [['csv_file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'csv', 'maxFiles' => 1, 'mimeTypes' => 'text/plain'],
        ];
    }

    /**
     * Validate form
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->validate();
    }

    public function beforeValidate(): bool
    {
        $this->csv_file = UploadedFile::getInstance($this, 'csv_file');

        return parent::beforeValidate();
    }

    /**
     * Get user options for csv handler
     *
     * @return CsvConfig
     */
    public function getCsvConfig(): CsvConfig
    {
        $dto = new CsvConfig();

        $dto->delimiter = $this->delimiter;
        $dto->enclosure = $this->enclosure;
        $dto->header = $this->getFields();
        $dto->file = $this->getCsvFile();
        $dto->skipHeader = (bool) $this->isSkipFirstLine;

        $options = new ImportOptions();
        $options->isUpdate = (bool) $this->isUpdate;
        $options->isEnable = (bool) $this->isEnable;
        $options->isReplaceSlug = (bool) $this->isReplaceSlug;

        $dto->options = $options;

        return $dto;
    }

    /**
     * Gets unique fields for custom csv header
     * Нужно для обработчика цсв, он неумеет в одинаковые названия полей.
     *
     * @return string[]
     */
    private function getFields(): array
    {
        return \array_map(function ($field, $index) {
            if ($field === 'skip') {
                return "skip{$index}";
            }

            return $field;
        }, $this->fields, \array_keys($this->fields));
    }

    private function getCsvFile(): ?\SplFileInfo
    {
        $file = null;

        if ($this->csv_file !== null) {
            return new \SplFileInfo($this->csv_file->tempName);
        } elseif ($this->csv_text !== '') {
            $filepath = Yii::getAlias('@runtime/uploaded_categories.csv');
            \file_put_contents($filepath, $this->csv_text);

            \register_shutdown_function(function() use($filepath) {
                if (\is_file($filepath)) {
                    \unlink($filepath);
                }
            });

            return new \SplFileInfo($filepath);
        }

        return $file;
    }
}
