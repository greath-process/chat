<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$arComponentParameters = [
    'GROUPS' => [],

    'PARAMETERS' => [
        'MESS_COUNT' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('MESS_COUNT'),
            'TYPE' => 'STRING',
            'DEFAULT' => ''
        ],
        'MESS_EXT' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('MESS_EXT'),
            'TYPE' => 'STRING',
            'DEFAULT' => ''
        ],
        'MESS_FILESIZE' => [
            'PARENT' => 'BASE',
            'NAME' => GetMessage('MESS_FILESIZE'),
            'TYPE' => 'STRING',
            'DEFAULT' => ''
        ],
    ],
];