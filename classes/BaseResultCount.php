<?php defined('SYSPATH') OR die('No direct access allowed.');
/*
28.11.2025
Класс для передачи результатов выполнения команд
*/
class BaseResultCount extends BaseResult
{
    public $countResult; // количество
    
    public function __construct($result = -1, $mess = null, $count = 0)
    {
        parent::__construct($result, $mess);
        $this->countResult = $count;
    }
}