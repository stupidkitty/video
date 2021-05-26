<?php

namespace SK\VideoModule\Admin\Controller;

use League\Csv\InvalidArgument;
use Psr\EventDispatcher\EventDispatcherInterface;
use SK\VideoModule\Admin\Form\CategoriesImportForm;
use SK\VideoModule\Category\Import\Csv\CsvHandler;
use SK\VideoModule\Category\Import\Event\RowHandleFailedEvent;
use SK\VideoModule\Category\Import\Event\RowHandleSuccessEvent;
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
    public function actionIndex(Request $request, CsvHandler $csvHandler, EventDispatcherInterface $eventDispatcher): string
    {
        $form = new CategoriesImportForm;
        $isProcessed = false;
        $handledRowsNum = 0;
        $errors = [];

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

        return $this->render('index', [
            'isProcessed' => $isProcessed,
            'handledRowsNum' => $handledRowsNum,
            'errors' => $errors,
            'form' => $form,
        ]);
    }
}
