<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Класс для хранения формата данных идентификатора.
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
        $this->id = isset($data[0]) ? (int)$data[0] : null;
        $this->field1 = isset($data[1]) ? trim($data[1]) : null;
        $this->field2 = isset($data[2]) ? trim($data[2]) : null;
        $this->field3 = isset($data[3]) ? trim($data[3]) : null;
        $this->field4 = isset($data[4]) ? trim($data[4]) : null;
        $this->code = isset($data[5]) ? trim($data[5]) : null;
        $this->status = isset($data[6]) ? (int)$data[6] : null;
    }

    /**
     * Проверяет, что объект содержит все необходимые данные для вставки.
     * @return bool
     */
    public function isValid() {
        // Проверяем, что code не пустой
        if (empty($this->code)) {
            return false;
        }
        
        // Проверяем, что status не пустой и является числом
        if (empty($this->status) || !is_numeric($this->status)) {
            return false;
        }

        // Проверяем обязательные поля (имя или фамилия)
        if (empty($this->field1) && empty($this->field2)) {
            return false;
        }

        $patterns = array(
            1 => '/^[A-F\d]{10}$/i',   // RFID
            2 => '/^[A-F\d]{10}$/i',   // FP
            4 => '/^[ABEKMHOPCTYX\d]{5,10}$/i', // ГРЗ
            5 => '/^\d{3,10}$/',       // FaceID
        );

        $pattern = isset($patterns[$this->status]) ? $patterns[$this->status] : '/^\d{3,10}$/';

        return preg_match($pattern, $this->code) === 1;
    }

    /**
     * Проверяет, уникальна ли карта в БД.
     * @param Database $db
     * @return bool
     * @throws Exception
     */
    public function isUnique($db) {
        // Если code пустой, считаем что карта не уникальна (чтобы не выполнять запрос с пустым значением)
        if (empty($this->code)) {
            return false;
        }
        
        $sql = 'SELECT COUNT(*) AS cnt FROM CARD WHERE ID_CARD = \':card\' AND ID_CARDTYPE = :type';
        $result = DB::query(Database::SELECT, $sql)
            ->param(':card', $this->code)
            ->param(':type', $this->status)
            ->execute($db);
        
        $count = $result->get('CNT');
		//echo Debug::vars('76', $result);exit;
        return (int)$count == 0;
    }

    /**
     * Создает массив данных для вставки в таблицы PEOPLE.
     * @param int $id_org
     * @return array
     */
    public function toPeopleData($id_org) {
        return array(
            'id_org' => $id_org,
            'surname' => !empty($this->field1) ? $this->field1 : '',
            'name' => !empty($this->field2) ? $this->field2 : '',
            'patronymic' => !empty($this->field3) ? $this->field3 : '',
            'note' => !empty($this->field4) ? $this->field4 : '',
            'sysnote' => "Old id_pep={$this->id}, old card='{$this->code}', old cardtype={$this->status}",
        );
    }

    /**
     * Создает массив данных для вставки в таблицу CARD.
     * @param int $id_pep
     * @return array
     */
    public function toCardData($id_pep) {
        return array(
            'id_card' => $this->code,
            'id_pep' => $id_pep,
            'id_cardtype' => $this->status,
            'note' => 'Import',
            'timestart' => 'now',
            'timeend' => '+365 days',
            'status' => 0,
            'active' => 1,
            'flag' => 0,
        );
    }
}
