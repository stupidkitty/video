# Videos
Yii2 video module

## App configure module
```
'modules' => [
    'videos' => [
        'class' => SK\VideoModule\Module::class,
        //'viewPath' => '@app/views/videos',
        //'controllerNamespace' => 'SK\VideoModule\Admin',
    ],
],
```

## Migrations
```
config:
'controllerMap' => [
    'migrate' => [
        'class' => yii\console\controllers\MigrateController::class,
        'migrationNamespaces' => [],
        'migrationPath' => [
            '@vendor/stupidkitty/video/src/Migration',
        ],
    ],
],
```
or composer:
```
"scripts": {
    "post-update-cmd": [
        "yes | php yii migrate --migrationPath=@vendor/stupidkitty/video/src/Migration"
    ],
    "post-install-cmd": [
        "yes | php yii migrate --migrationPath=@vendor/stupidkitty/video/src/Migration"
    ]
}
```
