<?php defined('SYSPATH') or die('No direct script access.');

class EximdataImport {

    /** @var Database */
    private $db;

    public function __construct() {
        $this->db = Database::instance('fb');
    }

    public function importFromCsv($filePath, $id_org) {
        $result = new BaseResultCount(-1, null, 0);
        $lines = array();

        if (!file_exists($filePath)) {
            $result->errorMessage = 'Файл не найден: ' . $filePath;
            return $result;
        }

        // 1. Чтение и парсинг CSV
        if (($fp = fopen($filePath, "r")) !== FALSE) {
            $rowCount = 0;
            while (($data = fgetcsv($fp, 0, ";")) !== FALSE) {
                $rowCount++;
                
                // Проверяем, что строка не пустая
                $hasData = false;
                foreach ($data as $field) {
                    if (!empty(trim($field))) {
                        $hasData = true;
                        break;
                    }
                }
                
                if (!$hasData) {
                    continue;
                }
                
                if (count($data) < 7) {
                    while (count($data) < 7) {
                        $data[] = '';
                    }
                }
                
                try {
                    $identifier = new IdentifierEE($data);
                    $lines[] = $identifier;
                } catch (Exception $e) {
                    $result->errorMessage = 'Ошибка в строке ' . $rowCount . ': ' . $e->getMessage();
                    fclose($fp);
                    return $result;
                }
            }
            fclose($fp);
        } else {
            $result->errorMessage = 'Не удалось открыть файл для чтения: ' . $filePath;
            return $result;
        }

        if (empty($lines)) {
            $result->errorMessage = 'Файл пуст или не содержит корректных данных.';
            return $result;
        }

        // 2. Предварительная валидация
        $validationErrors = array();
        $uniqueErrors = array();
        $validLines = array();

        foreach ($lines as $key => $identifier) {
            if (empty($identifier->code)) {
                $validationErrors[] = "Строка " . ($key+1) . ": Пропущен номер карты.";
                continue;
            }
            
            if (empty($identifier->status)) {
                $validationErrors[] = "Строка " . ($key+1) . ": Пропущен тип карты.";
                continue;
            }

            if (!$identifier->isValid()) {
                $validationErrors[] = "Строка " . ($key+1) . ": Неверный формат идентификатора '{$identifier->code}' для типа {$identifier->status}.";
                continue;
            }

            try {
                if (!$identifier->isUnique($this->db)) {
                    $uniqueErrors[] = "Строка " . ($key+1) . ": Карта '{$identifier->code}' уже существует в системе.";
                    continue;
                }
            } catch (Exception $e) {
                $validationErrors[] = "Строка " . ($key+1) . ": Ошибка проверки уникальности карты '{$identifier->code}': " . $e->getMessage();
                continue;
            }

            if (empty($identifier->field2) && empty($identifier->field1)) {
                $validationErrors[] = "Строка " . ($key+1) . ": Не указаны имя или фамилия.";
                continue;
            }
            
            $validLines[] = $identifier;
        }

        if (!empty($validationErrors) || !empty($uniqueErrors)) {
            $allErrors = array_merge($validationErrors, $uniqueErrors);
            $result->errorMessage = "Найдены ошибки в данных. Импорт отменен.<br>- " . implode("<br>- ", $allErrors);
            return $result;
        }

        // 3. Вставка в БД в рамках одной транзакции
        try {
            // ПРАВИЛЬНЫЙ МЕТОД ДЛЯ KOHANA 3.x
            $this->db->begin();

            $insertedCount = 0;
            foreach ($validLines as $identifier) {
                try {
                    $newIdPep = $this->insertPeople($identifier, $id_org);
                    $this->insertCard($identifier, $newIdPep);
                    $insertedCount++;
                } catch (Exception $e) {
                    throw new Exception('Ошибка вставки: ' . $e->getMessage());
                }
            }

            // ПРАВИЛЬНЫЙ МЕТОД ДЛЯ KOHANA 3.x
            $this->db->commit();

            $result->result = 0;
            $result->countResult = $insertedCount;
            $result->errorMessage = null;

        } catch (Exception $e) {
            // ПРАВИЛЬНЫЙ МЕТОД ДЛЯ KOHANA 3.x
            $this->db->rollback();
            $result->errorMessage = 'Ошибка импорта: ' . $e->getMessage();
            Kohana::$log->add(Log::ERROR, 'Импорт CSV провалился: ' . $e->getMessage());
        }

        return $result;
    }

    private function insertPeople($identifier, $id_org) {
        $sql = 'SELECT GEN_ID(GEN_PEOPLE_ID, 1) AS NEW_ID FROM RDB$DATABASE';
        $result = DB::query(Database::SELECT, $sql)->execute($this->db);
        $newIdPep = $result->get('NEW_ID');
        
        if (empty($newIdPep)) {
            throw new Exception('Не удалось получить новый ID для PEOPLE');
        }

        $data = $identifier->toPeopleData($id_org);

        $sql = 'INSERT INTO PEOPLE (ID_PEP, ID_DB, ID_ORG, SURNAME, NAME, PATRONYMIC, NOTE, SYSNOTE)
                VALUES (:id_pep, 1, :id_org, \':surname\', \':name\', \':patronymic\', \':note\', \':sysnote\')';

      $result=  DB::query(Database::INSERT, $sql)
            ->param(':id_pep', (int)$newIdPep)
            ->param(':id_org', $data['id_org'])
            ->param(':surname', $data['surname'])
            ->param(':name', $data['name'])
            ->param(':patronymic', $data['patronymic'])
            ->param(':note', $data['note'])
            ->param(':sysnote', $data['sysnote'])
            ->execute($this->db);
			
			
			
			
			
        return (int)$newIdPep;
    }


/**

string(3) "179"
array(9) (
    "id_card" => string(10) "1234567890"
    "id_pep" => integer 12539
    "id_cardtype" => integer 1
    "note" => string(6) "Import"
    "timestart" => string(3) "now"
    "timeend" => string(9) "+365 days"
    "status" => integer 0
    "active" => integer 1
    "flag" => integer 0
)
*/
    private function insertCard($identifier, $id_pep) {
        if (empty($identifier->code)) {
            throw new Exception('Невозможно вставить карту: код карты пустой');
        }
        
        $data = $identifier->toCardData($id_pep);
		$card=$data['id_card'];
		//$card='qwer';

        // $sql = 'INSERT INTO CARD (ID_CARD, ID_DB, ID_PEP, ID_ACCESSNAME, TIMESTART, TIMEEND, NOTE, STATUS, "ACTIVE", FLAG, ID_CARDTYPE)
                // VALUES (:id_card, 1, :id_pep, NULL, CURRENT_TIMESTAMP, CURRENT_DATE + 365, \':note\', :status, :active, :flag, :id_cardtype)';

        // DB::query(Database::INSERT, $sql)
            // ->param(':id_card', $card)
            // ->param(':id_pep', $data['id_pep'])
            // ->param(':note', $data['note'])
            // ->param(':status', $data['status'])
            // ->param(':active', $data['active'])
            // ->param(':flag', $data['flag'])
            // ->param(':id_cardtype', $data['id_cardtype'])
            // ->execute($this->db);
			
			
			 $sql = __('INSERT INTO CARD (ID_CARD, ID_DB, ID_PEP, ID_ACCESSNAME, TIMESTART, TIMEEND, NOTE, STATUS, "ACTIVE", FLAG, ID_CARDTYPE)
                VALUES (:id_card, 1, :id_pep, NULL, CURRENT_TIMESTAMP, CURRENT_DATE + 365, \':note\', :status, :active, :flag, :id_cardtype)', array(

 
            ':id_card' => $card,
            ':id_pep'=> $data['id_pep'],
            ':note' => $data['note'],
            ':status' => $data['status'],
            ':active' => $data['active'],
            ':flag'=> $data['flag'],
            ':id_cardtype' => $data['id_cardtype'],
			));
            try
			{
			DB::query(Database::INSERT, $sql)
			->execute(Database::instance('fb'));
			return true;
			} catch (Exception $e) {
			return false;
			}
		
		
			
			
			
			
			
    }

    /**
     * Импорт дерева организаций из JSON.
     */
    public function importTree($filePath, $id_parent) {
        $result = new BaseResultCount(-1, null, 0);

        $content = unserialize(file_get_contents($filePath));
        if ($content === false) {
            $result->errorMessage = 'Не удалось десериализовать файл.';
            return $result;
        }

        $tree = Arr::get($content, 'org', array());
        $people = Arr::get($content, 'people', array());
        $cards = Arr::get($content, 'card', array());
        $about = Arr::get($content, 'about', array());
        $uid = Arr::get($about, 'uid');

        if (empty($uid)) {
            $result->errorMessage = 'В файле отсутствует уникальный идентификатор (UID).';
            return $result;
        }

        try {
            // ПРАВИЛЬНЫЙ МЕТОД ДЛЯ KOHANA 3.x
            $this->db->begin();

            if ($this->isImportExists($uid)) {
                throw new Exception('Повторная вставка данных с UID "' . $uid . '" запрещена.');
            }

            $insertedOrgs = $this->insertOrganizations($tree, $id_parent, $uid);
            if ($insertedOrgs == 0 && !empty($tree)) {
                throw new Exception('Не удалось вставить организации.');
            }

            $insertedPeople = $this->insertPeopleFromTree($people, $uid);
            if ($insertedPeople == 0 && !empty($people)) {
                throw new Exception('Не удалось вставить людей.');
            }

            $insertedCards = $this->insertCardsFromTree($cards, $uid);
            if ($insertedCards == 0 && !empty($cards)) {
                throw new Exception('Не удалось вставить карты.');
            }

            // ПРАВИЛЬНЫЙ МЕТОД ДЛЯ KOHANA 3.x
            $this->db->commit();

            $result->result = 0;
            $result->countResult = $insertedOrgs;
            $result->errorMessage = "Успешно добавлено: {$insertedOrgs} организаций, {$insertedPeople} людей, {$insertedCards} карт.";

        } catch (Exception $e) {
            // ПРАВИЛЬНЫЙ МЕТОД ДЛЯ KOHANA 3.x
            $this->db->rollback();
            $result->errorMessage = 'Ошибка импорта дерева: ' . $e->getMessage();
            Kohana::$log->add(Log::ERROR, 'Импорт дерева провалился: ' . $e->getMessage());
        }

        return $result;
    }

    private function isImportExists($uid) {
        $sql = 'SELECT COUNT(*) AS cnt FROM ORGANIZATION WHERE DIVCODE LIKE :uid_pattern';
        $count = DB::query(Database::SELECT, $sql)
            ->param(':uid_pattern', '%' . $uid . '%')
            ->execute($this->db)
            ->get('CNT');
        return (int)$count > 0;
    }

    private function insertOrganizations($tree, $id_parent, $uid) {
        if (empty($tree)) {
            return 0;
        }

        $prepared = array();
        $parentsMap = array();

        foreach ($tree as $item) {
            $id = (int)$item['id'];
            $guid = $uid . $id;
            $prepared[$id] = array(
                'name' => $item['title'],
                'parent' => (int)$item['parent'],
                'guid' => $guid,
                'parentGuid' => null,
            );
            $parentsMap[$id] = $guid;
        }

        foreach ($prepared as $id => &$item) {
            if ($item['parent'] > 0 && isset($parentsMap[$item['parent']])) {
                $item['parentGuid'] = $parentsMap[$item['parent']];
            }
        }
        unset($item);

        $insertCount = 0;
        foreach ($prepared as $item) {
            $sql = 'INSERT INTO ORGANIZATION (ID_DB, NAME, ID_PARENT, FLAG, DIVCODE)
                    VALUES (1, :name, :id_parent, 0, :divcode)';
            DB::query(Database::INSERT, $sql)
                ->param(':name', iconv('UTF-8', 'windows-1251', $item['name']))
                ->param(':id_parent', $id_parent)
                ->param(':divcode', $item['guid'])
                ->execute($this->db);
            $insertCount++;
        }

        foreach ($prepared as $item) {
            if (!empty($item['parentGuid'])) {
                $sql = 'UPDATE ORGANIZATION SET ID_PARENT = (
                            SELECT ID_ORG FROM ORGANIZATION WHERE DIVCODE = :parent_guid
                        ) WHERE DIVCODE = :guid';
                DB::query(Database::UPDATE, $sql)
                    ->param(':parent_guid', $item['parentGuid'])
                    ->param(':guid', $item['guid'])
                    ->execute($this->db);
            }
        }

        return $insertCount;
    }

    private function insertPeopleFromTree($people, $uid) {
        if (empty($people)) {
            return 0;
        }

        $insertCount = 0;
        foreach ($people as $person) {
            $tabnum = $uid . (isset($person['TABNUM']) ? $person['TABNUM'] : '');
            $orgDivcode = $uid . (isset($person['ID_ORG']) ? $person['ID_ORG'] : '');
            
            $sql = 'INSERT INTO PEOPLE (
                        ID_DB, ID_ORG, SURNAME, NAME, PATRONYMIC, PHONEWORK, 
                        WORKSTART, WORKEND, "ACTIVE", FLAG, LOGIN, PSWD, PEPTYPE,
                        POST, NOTE, ID_AREA, SYSNOTE, TABNUM, AUTHMODE
                    ) VALUES (
                        1,
                        (SELECT ID_ORG FROM ORGANIZATION WHERE DIVCODE = :org_divcode),
                        :surname, :name, :patronymic, :phonework,
                        \'9:00:00\', \'18:00:00\', 1, 0, \'\', \'\', 0,
                        :post, :note, 0, :sysnote, :tabnum, 0
                    )';

            DB::query(Database::INSERT, $sql)
                ->param(':org_divcode', $orgDivcode)
                ->param(':surname', isset($person['SURNAME']) ? $person['SURNAME'] : '')
                ->param(':name', isset($person['NAME']) ? $person['NAME'] : '')
                ->param(':patronymic', isset($person['PATRONYMIC']) ? $person['PATRONYMIC'] : '')
                ->param(':phonework', isset($person['PHONEWORK']) ? $person['PHONEWORK'] : '')
                ->param(':post', isset($person['POST']) ? $person['POST'] : '')
                ->param(':note', isset($person['NOTE']) ? $person['NOTE'] : '')
                ->param(':sysnote', isset($person['SYSNOTE']) ? $person['SYSNOTE'] : '')
                ->param(':tabnum', $tabnum)
                ->execute($this->db);

            $insertCount++;
        }

        return $insertCount;
    }

    private function insertCardsFromTree($cards, $uid) {
        if (empty($cards)) {
            return 0;
        }

        $insertCount = 0;
        $errors = array();

        foreach ($cards as $card) {
            $tabnum = $uid . (isset($card['TABNUM']) ? $card['TABNUM'] : '');
            
            try {
                $sql = 'INSERT INTO CARD (
                            ID_CARD, ID_DB, ID_PEP, TIMESTART, TIMEEND, 
                            NOTE, STATUS, "ACTIVE", FLAG, ID_CARDTYPE
                        ) VALUES (
                            :id_card, 1,
                            (SELECT ID_PEP FROM PEOPLE WHERE TABNUM = :tabnum),
                            :timestart, :timeend,
                            :note, :status, :active, :flag, :id_cardtype
                        )';

                DB::query(Database::INSERT, $sql)
                    ->param(':id_card', isset($card['ID_CARD']) ? $card['ID_CARD'] : '')
                    ->param(':tabnum', $tabnum)
                    ->param(':timestart', isset($card['TIMESTART']) ? $card['TIMESTART'] : 'now')
                    ->param(':timeend', isset($card['TIMEEND']) ? $card['TIMEEND'] : '')
                    ->param(':note', isset($card['NOTE']) ? $card['NOTE'] : '')
                    ->param(':status', isset($card['STATUS']) ? (int)$card['STATUS'] : 0)
                    ->param(':active', isset($card['ACTIVE']) ? (int)$card['ACTIVE'] : 1)
                    ->param(':flag', isset($card['FLAG']) ? (int)$card['FLAG'] : 0)
                    ->param(':id_cardtype', isset($card['ID_CARDTYPE']) ? (int)$card['ID_CARDTYPE'] : 1)
                    ->execute($this->db);

                $insertCount++;
            } catch (Exception $e) {
                $cardId = isset($card['ID_CARD']) ? $card['ID_CARD'] : 'unknown';
                $errors[] = "Не удалось вставить карту '{$cardId}': " . $e->getMessage();
                Kohana::$log->add(Log::WARNING, 'Ошибка вставки карты: ' . $e->getMessage());
            }
        }

        if (!empty($errors)) {
            Kohana::$log->add(Log::ERROR, 'Ошибки при вставке карт: ' . implode('; ', $errors));
        }

        return $insertCount;
    }
}
