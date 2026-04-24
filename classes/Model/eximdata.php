<?php defined('SYSPATH') OR die('No direct access allowed.');

class Model_eximdata extends Model
{

	//обновление времени начала и окончания работы для пиплов указанной организации 23.08.2022
	public function setWorkTime($timeStart, $timeEnd, $id_org)
	{
		$sql='update people p set
		p.workstart=\''.$timeStart.'\',
		p.workend=\''.$timeEnd.'\'
		where p.id_org='.$id_org;
		Log::instance()->add(Log::NOTICE, 'Обновление времен работы для отчетов: '.$sql);
		$query = DB::query(Database::UPDATE, $sql)
		->execute(Database::instance('fb'));
		return;
	}
	
	
	public static function unique_org ($id_org) // проверка наличия id_org в базе данных. Вдруг такого id_org нет...
	{
				// Check if the id_org already exists in the database
	$sql='select ID_ORG from ORGANIZATION where ID_ORG='.$id_org;
	return $id_org == DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->get('ID_ORG');
	}
	
	
	/** 30.11.2025 проверка, что ни один из номеров карт не присутвует в базе данных
	*@input массив данных для импорта
	*@output true - все в порядке, номеров карт в базе данных нет, false - номер карты в базе данных есть. Возвращает список карт, которые есть в базе данных
	*/
	public static function uniqueListCard($list)
	{
		/* foreach($list as $key=>$value)
		{
			
			
		} */
		return true;
	}
	
	
	public static function unique_card ($card, $type) // проверка наличия номера карты. Вдруг такой идентификатор уже у кого-то есть. True - идентификатор есть в БД, false - идентификатора нет в БД
	{
		//echo Debug::vars('47',$card, $type );exit;		
	$sql='select ID_CARD from CARD where ID_CARD=\''.$card.'\'
		and ID_CARDTYPE='.$type;
	//echo Debug::vars('49', $sql);exit;
	return !($card == DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->get('ID_CARD'));
	}
	
	public function send_file ($file)// скачать указанный файл в браузер
	{
		//https://habr.com/ru/post/151795/
		/* $file = $name;
		header ("Content-Type: application/force-download");
		header ("Accept-Ranges: bytes");
		header ("Content-Length: ".filesize($file));
		header ("Content-Disposition: attachment; filename=".basename($file));  
		readfile($file);
		return basename($file); */
		
		if (file_exists($file)) {
    // сбрасываем буфер вывода PHP, чтобы избежать переполнения памяти выделенной под скрипт
    // если этого не сделать файл будет читаться в память полностью!
    if (ob_get_level()) {
      ob_end_clean();
    }
    // заставляем браузер показать окно сохранения файла
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    // читаем файл и отправляем его пользователю
    readfile($file);
    //exit;
  }
  
	}
	
	
	public  function getFileNameFromIdOrg ($id_org) // получение название организации по id_org
	{
				
	$sql='select NAME from ORGANIZATION where ID_org=\''.$id_org.'\'';
	return DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->get('NAME');
	}
	
	
	

	public function export ($id_org) // экспорт людей из указанной организации
	{
		$sql='select p.id_pep, p.surname, p.name, p.patronymic, p.note, c.id_card, c.id_cardtype from people p
			join card c on c.id_pep=p.id_pep
			where p.id_org='.$id_org;
		return DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
	}
	
	
	
	/**13.10.2024 Экспорт людей из указанной родительской организации. Будет выполнен экспорт из указанной организации и всех нижестоящих
	
	*/
	public function exportPeopleFromParentOrg($id_org)
	{
		$sql=' SELECT p.id_pep, p.id_org, p.name, p.surname, p.patronymic, p.phonework, p.note, p.post, p.tabnum FROM ORGANIZATION_GETCHILD(1, '.$id_org.') og
			join people p on p.id_org=og.id_org
			 where p."ACTIVE">0';
		
		return DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();

	}		
	
	/**13.10.2024 Экспорт карт из указанной родительской организации. Будет выполнен экспорт из указанной организации и всех нижестоящих
	
	*/
	public function exportCardFromParentOrg($id_org)
	{
		$sql=' SELECT c.id_card, c.id_pep, p.tabnum, c.timestart, c.timeend, c.note, c.status, c."ACTIVE", c.flag, c.id_cardtype FROM ORGANIZATION_GETCHILD(1, '.$id_org.') og
			 join people p on p.id_org=og.id_org
			 join card c on c.id_pep=p.id_pep
			 where p."ACTIVE">0';
		
		return DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();

	}		
	
	
	
	public function exportTree ($id_org) // экспорт организаций указанной организации
	{
		$sql='select p.id_pep, p.surname, p.name, p.patronymic, p.note, c.id_card, c.id_cardtype from people p
			join card c on c.id_pep=p.id_pep
			where p.id_org='.$id_org;
		return DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
	}
	
	
	
	public function editOrg ($id_org) // редактирование данных организации 23.08.2022
	{
		$sql='select p.id_pep, p.surname, p.name, p.patronymic, p.note, c.id_card, c.id_cardtype from people p
			join card c on c.id_pep=p.id_pep
			where p.id_org='.$id_org;
		return DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
	}
	
	

	public function getChild ($id_org = 1) // получить список дочерних организаций
	{
		if(is_null($id_org)) $id_org=1;
		$sql='select * from organization o 
		where o.id_parent='.$id_org;
		$sql='select o.id_org, o.name, count(o2.id_org) from organization o
		left join organization o2 on o2.id_parent=o.id_org
        where o.id_parent='.$id_org.'
        group by  o.id_org, o.name';
		return DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
	}

	public function countChild ($id_org = 1) // количество дочерних организаций
	{
		if(is_null($id_org)) $id_org=1;
		$sql='select count(*) from organization o 
		where o.id_parent='.$id_org;
		return DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->get('COUNT');
	}

	public function countPeopleInOrg () // подстчет количество людей в организации
	{
		$sql='select p.id_org, count(p.id_pep) from people p
		group by p.id_org';
		$temp= DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
			$resalt=array();
		foreach ($temp as $key=>$value)
		{
			$result[Arr::get($value, 'ID_ORG')]=Arr::get($value, 'COUNT');
		}
		return $result;
	}
	
	
	public function getNewIdPep()// получить ID_pep для вновь вставляемого пользователя
	{
		$sql='select gen_id(gen_people_id, 1) FROM RDB$DATABASE';
		return DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->get('GEN_ID');
	}

	/*
		$list[0] - старый id_pep
		$list[1] - старый surname
		$list[2] - старый name
		$list[3] - старый patronymic
		$list[4] - старый note
		$list[5] - старый id_card
		$list[6] - старый id_cardtype
		 p.surname, p.name, p.patronymic, p.note, c.id_card, c.id_cardtype
	return new_id_pep
	*/
	
	public function insertPeople($value, $id_org)// добавить ОДНОГО ФИО в таблицы PEOPLE и CARD
	{
		//echo Debug::vars('232',$value, $id_org );//exit;
		//echo Debug::vars('233',$this->keyValidation($value));exit;
					
			if(!$this->keyValidation($value)->result)//валидация выполнена успешно, начинаю запись контакта и карты в БД СКУД
			{
				
					$new_id_pep=$this->getNewIdPep();
					$this->insertFIO($value, $new_id_pep, $id_org);// добавление в СКУД ФИО, Note для указанного id_pep
					$this->addCard($value, $new_id_pep);// присвоение номера карты указанном пользователю
					//$this->addSysnote();// добавление записи в поле SYSNOTE по результатам вставки пользователя.
				
				return $new_id_pep;
				} else {
					echo Debug::vars('243');exit;
					return false;
				}
					
	}
	
	
	/**27.11.2025 Проверка номера идентификатора в зависимости от его типа.
	* @input $key массив с  набором данных:
	* p.id_pep, p.surname, p.name, p.patronymic, p.note, c.id_card, c.id_cardtype
	* @output результат выполнения и код ошибки
	*/
	private function keyValidation($key)
	{
		
		switch(Arr::get($key, 6))
		{
			
			case 1://RFID
				$rule='/^[A-F\d]{10}+$/';
			break;
			
			case 2://FP
				$rule='/^[A-F\d]{10}+$/';
			break;
			
			case 4://ГРЗ
				$rule='/^[ABEKMHOPCTYX\d]{5,10}$/';
				//$rule='/^[A-F\d]{10}+$/';
			break;
			
			case 5://FaceID
				$rule='/^\d{3,10}$/';
			break;
			
			default:
			$rule='/^\d{3,10}$/';
			
			break;
			
		}
		
			$data=Validation::factory($key);
				$data->rule(0, 'digit')
				//	->rule(0, 'not_empty')
				//	->rule(1, 'max_length', array(':value', 50))
				//	->rule(2, 'max_length', array(':value', 50))
				//	->rule(3, 'max_length', array(':value', 50))
					->rule(5, 'regex', array(':value', $rule)) // https://regex101.com/
					
					;
		
		$result = new baseResult();
		if($data->check())
		{
			$result->result=0;
			
		} else {
			$result->result=-1;
			$result->errorMessage=implode(",", $data->errors('validation'));
			
		}
		
		return $result;
		
	}
	
	
	public function checkCard($card, $cardType)// проверка наличия вновь добавляемого номера карты. TRUE - карта имеется в БД, FALSE - карты нет в БД
	{
		$sql='select id_card from card c
		where c.id_card=\''.$card.'\' and c.id_cardtype='.$cardType;
		return strtoupper($card) == DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->get('ID_CARD');
	}

	public function insertFIO($value, $id_pep, $id_org)
	{
		//echo Debug::vars('325',iconv('windows-1251','UTF-8', implode(",",$value)));//exit;
		$sysnote='Old id_pep='.Arr::get($value, 0).', old card="'.Arr::get($value, 5).'", old cardtype='.Arr::get($value, 6);
	//echo Debug::vars('329', $value);//exit;	
		$sql='INSERT INTO PEOPLE (ID_PEP,ID_DB,ID_ORG,SURNAME,NAME,PATRONYMIC,NOTE,SYSNOTE)
		VALUES ('.$id_pep.',1,'.$id_org.',\''.Arr::get($value, 1).'\',\''.Arr::get($value, 2).'\', \''.Arr::get($value, 3).'\',\''.Arr::get($value, 4).'\', \''.$sysnote.'\')';
		
		$sql=__('INSERT INTO PEOPLE (ID_PEP,ID_DB,ID_ORG,SURNAME,NAME,PATRONYMIC,NOTE,SYSNOTE)
		VALUES (:id_pep, 1,:id_org,\':surname\', \':name\', \':patronymic\',\':note\', \':sysnote\')', array(
				':id_pep'=>$id_pep,
				':id_org'=>$id_org,
				':surname'=>Arr::get($value, 'surname'),
				':name'=>Arr::get($value, 'name'),
				':patronymic'=>Arr::get($value, 'patronymic'),
				':note'=>Arr::get($value, 'note'),
				':sysnote'=>Arr::get($value, 'sysnote'),

			));	

		try
			{
			DB::query(Database::INSERT, $sql)
			->execute(Database::instance('fb'));
			} catch (Exception $e) {
			
			}
		
		return true;
	}
	
	public function addCard($value, $id_pep)//добавление карты для указанного id_pep
	{
		$note='Old id_pep='.Arr::get($value, 0).', old card="'.Arr::get($value, 5).'", old cardtype='.Arr::get($value, 6);
		$note='Import';
		//echo Debug::vars('362', $value);exit;
		$sql='INSERT INTO CARD (ID_CARD,ID_DB,ID_PEP,ID_ACCESSNAME,TIMESTART,TIMEEND,NOTE,STATUS,"ACTIVE",FLAG,ID_CARDTYPE) 
		VALUES (\''.Arr::get($value, 'key').'\',1,'.$id_pep.',NULL,\'now\',CURRENT_DATE+365,\''.$note.'\',0,1,0,'.Arr::get($value, 'type').')';
		//echo Debug::vars('365', $sql);exit;
		Kohana::$log->add(Log::ERROR, '366 ' . $sql);
		try
			{
			DB::query(Database::INSERT, $sql)
			->execute(Database::instance('fb'));
			} catch (Exception $e) {
			
			}
	
		return true;
	}
	
	
	public function getOrgListForOnce($id_org) //список организаций (квартир) начиная с указанной.
	{
		
			
		//https://xhtml.ru/2022/html/tree-views/		
		$sql='SELECT  og.id_org, og.name, og.id_parent, og.flag FROM ORGANIZATION_GETCHILD(1, '.$id_org.')  og order by og.name';
		$res=array();	
		try
		{
			$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
			
			
		foreach ($query as $key=>$value)
		{
			$res[Arr::get($value, 'ID_ORG')]['id']=Arr::get($value, 'ID_ORG');
			$res[Arr::get($value, 'ID_ORG')]['title']=iconv('windows-1251','UTF-8', Arr::get($value, 'NAME'));
			$res[Arr::get($value, 'ID_ORG')]['parent']=Arr::get($value, 'ID_PARENT');
			$res[Arr::get($value, 'ID_ORG')]['busy']=Arr::get($value, 'ID_GARAGE');
			
		}
		$res[$id_org]['parent']=0;
			return $res;
		} catch (Exception $e) {
			Log::instance()->add(Log::ERROR, $e);
		}
		
	}

	/** 12.10.2024 вставка дерева в указанную организацию.
	* input массив элементов
	*485 => array(4) (
    *    "id" => string(3) "485"
    *    "title" => string(10) "Кв 45 е"
    *    "parent" => string(2) "20"
    *    "busy" => NULL
	*
	* $id_parent - id организации, куда необходимо вставить дерево
	* uid-текстовая константа, которая будет добавляться в divcode
	*output BaseResultCount();
	*/
	public function insertTree(array $tree, $id_parent, $uid)
	{
		$script=array();
		$dd=array();
		$guid=array();
		$importParent=array();// поиск "корня" вставляемого дерева.
		
		//добавляю в каждую строку уникальный guid
		foreach($tree as $key=>$value)
		{
			//$tree[$key]['guid']=$this->getGUID();
			$tree[$key]['guid']=$uid.Arr::get($value, 'id');
			if(Arr::get($value, 'parent') == 0) $importParent[]=Arr::get($value, 'id');
		}
		
		//поиск организации, у которой parent=0
		if(count($importParent) != 1) throw Exception('Дерево для вставки имеет ошибки в структуре');
		
		
		//вставка организаций в указанного родителя. Вставка происходит линейно, все организацию вставляются в указанного родителя
		foreach ($tree as $key=>$value)
		{
			$sql='INSERT INTO ORGANIZATION (ID_DB,NAME,ID_PARENT,FLAG,DIVCODE) VALUES (1,\''.iconv('UTF-8','windows-1251', Arr::get($value, 'title')).'\','.$id_parent.',0,\''.Arr::get($value, 'guid').'\')';
		
			try
			{
			
			 DB::query(Database::INSERT, $sql)
			->execute(Database::instance('fb'));
			
			} catch (Exception $e) {
			
			
			}
		}
		
	
		//расстановка зависимостей: добавляю parentGuid, который ссылается на guid родителя
		
		foreach($tree as $key=>$value)
		{
			foreach($tree as $key2=>$value2)
			{
				if(Arr::get($value2, 'parent')==Arr::get($value, 'id')) $tree[$key2]['parentGuid']=Arr::get($value, 'guid');
			}
			
		}
		
		//формирую sql запросы для расстановки зависимостей
		foreach ($tree as $key=>$value)
		{
			
			$sql='update organization o2
					set o2.id_parent=(
					select o.id_org from organization o
					where o.divcode=\''.Arr::get($value,'parentGuid' ).'\'
					)
					where o2.divcode=\''.Arr::get($value,'guid').'\'';
					
			try
			{
			
			 DB::query(Database::INSERT, $sql)
			->execute(Database::instance('fb'));
			
			} catch (Exception $e) {
				
			
			}

			
		}
		
		return new BaseResultCount(0, null, count($tree));
		
	}
	
	/**13.10.2024 Вставка контактов в дерево
	*
	*/
	public function insertContactInTree($people, $uid)
	{
		
		foreach($people as $key=>$value)
		{
			
			$sql='INSERT INTO PEOPLE (
					ID_DB,
					ID_ORG,
					SURNAME,
					NAME,
					PATRONYMIC,
					PHONEWORK,
					WORKSTART,
					WORKEND,
					"ACTIVE",
					FLAG,
					LOGIN,
					PSWD,
					PEPTYPE,
					POST,
					NOTE,
					ID_AREA,
					SYSNOTE,
					TABNUM,
					AUTHMODE)
					VALUES (
						1,
						 (select o.id_org from organization o
							where o.divcode= \''.$uid.Arr::get($value, 'ID_ORG').'\'),						
						\''.Arr::get($value, 'SURNAME').'\',
						\''.Arr::get($value, 'NAME').'\',
						\''.Arr::get($value, 'PATRONYMIC').'\',
						\''.Arr::get($value, 'PHONEWORK').'\',
						\'9:00:00\',
						\'18:00:00\',
						1,
						0,
						\'\',
						\'\',
						0,
						\''.Arr::get($value, 'POST').'\',
						\''.Arr::get($value, 'NOTE').'\',
						0,
						\''.Arr::get($value, 'SYSNOTE', '').'\',
						\''.$uid.Arr::get($value, 'TABNUM', '').'\',
						0
						)';
					
			
					try
						{
						DB::query(Database::INSERT, $sql)
						->execute(Database::instance('fb'));
						} catch (Exception $e) {
							return BaseResultCount(525, 'insertContactInTree', 0);
							throw new Exception($e->getMessage(), 463);
						}
				
				}
			return new BaseResultCount(0, null, count($people));
	}
	
	
	/**13.10.2024 Вставка карт для контактов в дерево
	*
	*/
	public function insertCardInTree($card, $uid)
	{
		$cardIsExist=array();
		foreach($card as $key=>$value)
		{
			//сначала делаю проверку: может, карта существует?
			$sql='select c.id_card, p.id_pep, p.surname, p.name, p.patronymic, o.name as orgname from card c
			join PEOPLE p on p.id_pep=c.id_pep
			join organization o on o.id_org=p.id_org
			where c.id_card=\''.Arr::get($value, 'ID_CARD').'\'';
			
			$query = Arr::flatten(DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array());
					
			if($query){
				$cardIsExist[]=array(
					//	'source'=>$value,
					//	'query'=>$query,
						'mess'=>__('Карта ":card" принадлежит :f :i :o из организации  ":org"', array(
							':card'=>Arr::get($value, 'ID_CARD'),
							':f'=>iconv('windows-1251', 'utf-8', Arr::get($query, 'NAME')),
							':i'=>iconv('windows-1251', 'utf-8', Arr::get($query, 'SURNAME')),
							':o'=>iconv('windows-1251', 'utf-8', Arr::get($query, 'PATRONYMIC')),
							':org'=>iconv('windows-1251', 'utf-8', Arr::get($query, 'ORGNAME')),
							)),
							);
			} else {
				
				$sql='INSERT INTO CARD (ID_CARD,ID_DB,ID_PEP,TIMESTART,TIMEEND,NOTE,STATUS,"ACTIVE",FLAG,ID_CARDTYPE) 
					VALUES (
						\''.Arr::get($value, 'ID_CARD').'\',
						1,
						(
						   select p.id_pep from people p where p.tabnum containing \''.$uid.Arr::get($value, 'TABNUM').'\'
                        			
						),
						\''.Arr::get($value, 'TIMESTART').'\',
						\''.Arr::get($value, 'TIMEEND').'\',
						\''.Arr::get($value, 'NOTE').'\',
						'.Arr::get($value, 'STATUS').',
						'.Arr::get($value, 'ACTIVE').',
						'.Arr::get($value, 'FLAG').',
						'.Arr::get($value, 'ID_CARDTYPE').'
						)';
						
					try
					{
						$query=DB::query(Database::INSERT, $sql)
						->execute(Database::instance('fb'));
					} catch (Exception $e) {
						//Driver does not support this function: driver does not support lastInsertId()
						//однако запись вставлена, и пусть будет так
						}
										
			}
		}
		
		return $cardIsExist;// возвращаю список карт, которые уже имеются в системе и не могут быть вставлены
	}
	
	
	public function deleteChildOrg($id_org)
	{
		$sql='delete from organization o
		where o.id_parent='.$id_org;
		$query = DB::query(Database::DELETE, $sql)
			->execute(Database::instance('fb'));
				
		return $query;
		
	}
	
	/**13.10.2024 удадленик пиплов из дочерних организаций
	*
	*/
	
	public function deletePeopleFromChildOrg($id_org)
	{
		$script=array();
		$sql='select og.id_org from ORGANIZATION_GETCHILD(1, '.$id_org.') og';
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
		
		//в цикле удаляю контакты из выбранных организаций
		foreach($query as$key=>$value)
		{
			$var[]=Arr::get($value, 'ID_ORG');
			$sql='delete from people p where p.id_org='.Arr::get($value, 'ID_ORG');
			$script[]=$sql;
			
			$query = DB::query(Database::DELETE, $sql)
			->execute(Database::instance('fb'));
			
		}
		return;
		
	}
	
	
	
	
	public function getGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    } else {
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }
	}
	public function getGUID_8(){
		if (function_exists('com_create_guid')){
			return com_create_guid();
		} else {
			mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
			$charid = strtoupper(md5(uniqid(rand(), true)));
			$hyphen = chr(45);// "-"
			$uuid = chr(123)// "{"
				.substr($charid, 0, 8)            
				.chr(125);// "}"
			return $uuid;
		}
	}
	
	
	/** получить ФИО для вставляемой карты, чтобы было понятно кому уже выдана карта
	*/
	
	public function getInforForCard($card)
	{
		$sql=__('select p.name, p.surname, p.patronymic, o.name as orgName from card c
			join people p on p.id_pep=c.id_pep
			join organization o on o.id_org=p.id_org
			where c.id_card=\':card\'', array(
				':card'=>$card,
			));
			
			
		$query = DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array();
			
			return Arr::get($query, 0);
	}

	/**28.11.2025 выявление повторного запуска вставки
	*если в divcode найдется uid - значит это повторная вставка
	*/
	public function detectRepeatRun($uid)
	{
		$result=new baseResult;
		$sql=__('select * from organization o
					where o.divcode like \'%:uid%\'', array(
				':uid'=>$uid,
			));
				
		try
		{		
			$query = DB::query(Database::SELECT, $sql)
				->execute(Database::instance('fb'))
				->as_array();
				
			if(count($query)){
					$result->result=-1;
					$result->errorMessage=__('repeat_run_import');
			} else {
					$result->result=0;
					
			}
		}  catch (Exception $e) {
			Log::instance()->add(Log::ERROR, $e);
			$result->result=-1;
			$result->errorMessage=$e->getMessage();

		}			
			
			return $result;
	}


	/**28.11.2025 удадление организации при ошибке вставки 
	*если в divcode найдется uid - значит это повторная вставка
	*/
	public function deleteOrgImportErr($uid)
	{
		$result=new baseResult;
		$sql=__('delete from organization o
					where o.divcode like \'%:uid%\'', array(
				':uid'=>$uid,
			));
				
		try
		{		
			$query = DB::query(Database::DELETE, $sql)
				->execute(Database::instance('fb'))
				;
			
		}  catch (Exception $e) {
			Log::instance()->add(Log::ERROR, $e);
			

		}			
			$result->result=0;
			$result->errorMessage=null;
			
			return $result;
	}



}
	

