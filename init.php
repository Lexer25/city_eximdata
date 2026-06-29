<?php defined('SYSPATH') or die('No direct script access.');
defined('EXIMDATA_VERSION') OR define('EXIMDATA_VERSION', '2.0.1');

Kohana::$config->load('menu')
    ->set('eximdata', array(
        'title' => 'Экспорт/импорт',
        'url' => 'eximdata',
        'icon' => 'fa-cog',
        'order' => 100,
       
    ));