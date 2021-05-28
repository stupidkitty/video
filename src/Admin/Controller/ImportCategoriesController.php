<?php

namespace SK\VideoModule\Admin\Controller;

use League\Csv\InvalidArgument;
use Psr\EventDispatcher\EventDispatcherInterface;
use SK\VideoModule\Admin\Form\CategoriesImportForm;
use SK\VideoModule\Category\Import\Csv\CsvHandler;
use SK\VideoModule\Category\Import\Event\RowHandleFailedEvent;
use SK\VideoModule\Category\Import\Event\RowHandleSuccessEvent;
use SK\VideoModule\Model\CategoryImportFeed;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Request;

class ImportCategoriesController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ];
    }

    /**
     * Импорт категорий через файл или текстовую форму.
     *
     * @param Request $request
     * @param CsvHandler $csvHandler
     * @return string
     * @throws InvalidArgument
     */
    public function actionIndex(Request $request, CsvHandler $csvHandler, EventDispatcherInterface $eventDispatcher, int $preset = 0): string
    {

        $isProcessed = false;
        $handledRowsNum = 0;
        $errors = [];

        $importFeed = CategoryImportFeed::find()
            ->where(['feed_id' => $preset])
            ->one();

        if ($importFeed === null) {
            $importFeed = new CategoryImportFeed();
        }

        $form = new CategoriesImportForm($importFeed);

        if ($form->load($request->post()) && $form->isValid()) {
            $eventDispatcher->addListener(RowHandleFailedEvent::NAME, function ($event) use (&$handledRowsNum, &$errors) {
                $handledRowsNum++;
                //dd($event);
                $errors[] = $event->getMessage();
            });

            $eventDispatcher->addListener(RowHandleSuccessEvent::NAME, function ($event) use (&$handledRowsNum) {
                $handledRowsNum++;
            });

            $handlerConfig = $form->getCsvConfig();
            $csvHandler->handle($handlerConfig);

            $isProcessed = true;
        }

        $presetListOptions = CategoryImportFeed::find()
            ->select(['name'])
            ->indexBy('feed_id')
            ->column();

        return $this->render('index', [
            'isProcessed' => $isProcessed,
            'handledRowsNum' => $handledRowsNum,
            'errors' => $errors,
            'form' => $form,
            'presetListOptions' => $presetListOptions,
            'preset' => $preset,
        ]);
    }
}
