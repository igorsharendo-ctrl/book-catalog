<?php

return [
    'manageCatalog' => [
        'type' => 2,
        'description' => 'Добавление, редактирование и удаление книг и авторов',
    ],
    'user' => [
        'type' => 1,
        'description' => 'Авторизованный пользователь каталога',
        'children' => [
            'manageCatalog',
        ],
    ],
];
