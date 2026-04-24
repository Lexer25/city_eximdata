<?php defined('SYSPATH') or die('No direct script access.');

/**30.11.2025 Класс для хранения формата данных идентификатора.

*/
class IdentifierEE {
    public $id;
    public $field1;
    public $field2;
    public $field3;
    public $field4;
    public $code;
    public $status;
    
    public function __construct(array $data) {
        $this->id = $data[0] ?? null;
        $this->field1 = $data[1] ?? null;
        $this->field2 = $data[2] ?? null;
        $this->field3 = $data[3] ?? null;
        $this->field4 = $data[4] ?? null;
        $this->code = $data[5] ?? null;
        $this->status = $data[6] ?? null;
    }
}

// Использование
/* $data = array(
    0 => "7175",
    1 => "",
    2 => "",
    3 => "",
    4 => "",
    5 => "7627DE001A",
    6 => "1"
);

$object = new MyClass($data); */