<?php defined('SYSPATH') OR die('No direct access allowed.');
/*
27.11.2025
Класс для передачи результатов выполнения команд
*/
class BaseResult
{
	public $result;//0-выполнено успешно, -1 - выполнено с ошибкой, >0 - специфический код ошибки
	public $errorMessage;//string описание ошибки
	
	
	
	public function __construct($result=-1, $mess=null)
    {
		$this->result=$result;
		$this->errorMessage=$mess;
		
	}
		
	
	
}
