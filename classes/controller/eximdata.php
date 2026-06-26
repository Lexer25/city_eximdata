<?php defined('SYSPATH') or die('No direct script access.');

/**13.10.2024 Пакет eximdata предназначен для экспорти и импорта данных.

*/
class Controller_eximdata extends Controller_Template{
	
	public $template = 'template';
	public $cache_dir = APPPATH . 'cache/';
	public $cacheFileName='dataimport';
	private $nameLen;
	private $surNameLen;
	private $patronymicLen;
	private $noteLen;
	
	
	public function before()
	{
			parent::before();
			$session = Session::instance();
			
			$this->nameLen=$this->_getFieldLen('NAME');
			$this->surNameLen=$this->_getFieldLen('SURNAME');
			$this->patronymicLen=$this->_getFieldLen('PATRONYMIC');
			$this->noteLen=$this->_getFieldLen('NOTE');
		//echo Debug::vars('24', $this->_getFieldLen('NAME'));exit;	
		//	echo Debug::vars('25', $this);exit;
	}
	
	
	public function action_index()
	{
		
		 // Получаем текущий запрос
        $request = $this->request;
        
        // Проверяем метод запроса
        if ($request->method() === HTTP_Request::GET) {
			
			   // Обработка GET запроса
           
			$id=$this->request->param('id');
		$orgList=Model::factory('eximdata')->getChild($id);
		$countChild=Model::factory('eximdata')->countChild($id);
		$countPeopleInOrg=Model::factory('eximdata')->countPeopleInOrg();

	

			$content = View::factory('eximpdata', array(
					'orgList'=>$orgList,
					'countChild'=>$countChild,
					'countPeopleInOrg'=>$countPeopleInOrg,
					));
					$this->template->content = $content;
         
        } elseif ($request->method() === HTTP_Request::POST) {
            // Обработка POST запроса
          return $this->action_import();

        } elseif ($request->method() === HTTP_Request::PUT) {
            // Обработка PUT запроса
            echo "Это PUT запрос";
        } elseif ($request->method() === HTTP_Request::DELETE) {
            // Обработка DELETE запроса
            echo "Это DELETE запрос";
        }
		
		
		

	}
	

	
	public function action_editOrg()//просмотр свойст организации и их редактирование 23.08.2022
	{
		$id=$this->request->param('id');
		$id_org=Validation::factory(array('id_org'=>$id));
		$id_org->rule('id_org', 'digit')
				->rule('id_org', 'not_empty')
				->rule('id_org', 'Model_eximdata::unique_org');
		if($id_org->check())
			{
				$nameOrg=Model::factory('eximdata')->getFileNameFromIdOrg($id);// получил название организации
				$list=Model::factory('eximdata')->export(Arr::get($id_org, 'id_org'));// получил список данных о сотрудниках для сохранения.
				}
			else {
			Session::instance()->set('e_mess', $id_org->errors('eximdata'));
			
			$this->redirect('/eximdata');
				
			}	
		
		$content = View::factory('org/view', array(
					'nameOrg'=>$nameOrg,
					'list'=>$list,
					'id_org'=>Arr::get($id_org, 'id_org')
					));
		$this->template->content = $content;
		
		
		
		
	}
	
	public function action_executor()// фукнция для обработки GET и POST запросов 23.08.2022
	{
		$post=Validation::factory($_POST);
		$post->rule('timeStart', 'regex', array(':value', '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'))
				->rule('timeEnd', 'regex', array(':value', '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'))
				->rule('updateWorkTime', 'not_empty')
				->rule('id_org', 'not_empty')
				->rule('id_org', 'digit')
				;
				if($post->check())
		{
				
				$res=Model::Factory('eximdata')->setWorkTime(Arr::get($post, 'timeStart'), Arr::get($post, 'timeEnd'),Arr::get($post, 'id_org') );
				Session::instance()->set('ok_mess', array('Обновление времен начала и завершения рабочего дня выполнено успешно.'));
				$this->redirect('/eximdata/editOrg/'.Arr::get($post, 'id_org'));
				
				
		} else {
				
			
			Session::instance()->set('e_mess', $post->errors('eximdata'));
			$this->redirect('/eximdata');
		}
	}

	public function action_export()
	{
		$id=$this->request->param('id');
			
		$file='file2.csv';	
		$id_org=Validation::factory(array('id_org'=>$id));
		$id_org->rule('id_org', 'digit')
				->rule('id_org', 'not_empty')
				->rule('id_org', 'Model_eximdata::unique_org');
		
		if($id_org->check())
			{
				$nameOrg=Model::factory('eximdata')->getFileNameFromIdOrg($id);// получил название организации как имя файла
				$file = preg_replace("([[:punct:] ])", '_', $nameOrg).'.csv';
				$list=Model::factory('eximdata')->export(Arr::get($id_org, 'id_org'));// получил список данных о сотрудниках для сохранения.
				//сохранение промежуточного файла
				$fp = fopen($file, 'w');
				
				foreach ($list as $fields) {
					fputcsv($fp, $fields, ';', '"');
				}
				if(!fclose($fp))
				{
					Session::instance()->set('err_mess', array('ok_mess'=>'Не могу сохранить файл '.$file));
				} 					

		
		$content = Model::Factory('eximdata')->send_file($file);
			} else {
				Session::instance()->set('e_mess', $id_org->errors('eximdata'));
				$this->redirect('/eximdata');
				
			}	
		
		exit;
	}
	
	/** 12.10.2024 экспорт дерева организаций
	*в этом процессе главное соблюст порядом добавления организаций, чтобы все выполнялось последовательно
	*
	*/
	public function action_exportTree()
	{
		$id=$this->request->param('id');//id головной организации
		$var1=time();
		$eximdata=Model::factory('eximdata');
		$about=array(
			'timestamp'=>$var1,
			'datestamp'=>date('d.m.Y H:i:s', $var1),
			'uid'=>$eximdata->getGUID_8(),//уникальный идентификатор формируемого массива данных.
			);
		
		$tree=$eximdata->getOrgListForOnce($id);// получил упорядоченный массив дерева организаций.
		$people=$eximdata->exportPeopleFromParentOrg($id);// получил список жильцов из указанной и дочерних организаций.
		$card=$eximdata->exportCardFromParentOrg($id);// получил список карт из указанной и дочерних организаций.
		
		$exportData=array(
			'about'=>$about,
			'org'=>$tree,
			'people'=>$people,
			'card'=>$card,
		);
				
				$nameOrg=Model::factory('eximdata')->getFileNameFromIdOrg($id);// получил название организации
				$filename = preg_replace("([[:punct:] ])", '_', $nameOrg).'.json';
				
				if(!file_put_contents($filename, serialize($exportData)))
				{
					Session::instance()->set('err_mess', array('ok_mess'=>'Не могу сохранить файл '.$filename));
				} 					

		
		
			$content = Model::Factory('eximdata')->send_file($filename);
		
				
		
	}
	/** 12.10.2024 Экспорт данных со всем вложенными папками для последующей вставки в базу данных.
	*
	*
	*/
	
	public function action_importTree()
	{
			$id_org=Arr::get($_POST, 'id_org2');
			
		 	$sourceFile = Arr::get($_FILES[$this->cacheFileName], 'name');//сохраняю имя файла для последующих комментариев
					
			$filename = $this->_uploadFile('json');//загружаю файл типа json
			$current = unserialize(file_get_contents($filename));		
	
	//запись дерева организаций в место вставки
		
			$eximdata=Model::factory('eximdata');
			
			$uid=Arr::get(Arr::get($current, 'about'), 'uid');//уникальный идентификатор пакета для вставки данных
			//проверка, что это не повторный запуск. Признак повторного запуска - наличие UID в guid организаций.
			
			if($eximdata->detectRepeatRun($uid)->result != 0)
			{
					
					//уже была попытка вставить эти данные. Надо вывести сообщение о прекращении работы.
					Session::instance()->set('e_mess', array('e_mess'=>__('Повторная вставка данных из файла ":filename" не допускается. Работа прекращается.', 
						array(
							':filename'=> $sourceFile,
							)
					))
					);
					
					//выход из импорта, вывод причины на экран
				$this->redirect('/eximdata');
				
			} else {
				//это первая вставка, продолжаю работу.
				
			}
			
			$var1=$eximdata->insertTree(Arr::get($current, 'org'), $id_org, $uid);
			
			if($var1->result==0) //если организации добавлены успешно, то продолжаю
			{
				$result['orgCount']=$var1->countResult;//количество успешно вставленных организаций.
			//если вставка прошла успешно, то начинаю добавлять контакты
				//добавляю контакты. Важно: в качестве id_org контакта необходимо искать организацию, у которой divcode=$uid<старый номер id_org>
				$var2=$eximdata->insertContactInTree(Arr::get($current, 'people'), $uid);
				if($var2->result == 0) //если контакты добавлены успешно
				{					
					$result['contactCount']=$var2->countResult;//количество успешно вставленных организаций.
					//добавление карт для сотрудников. При добавлении может быть коллизия: карта уже выдана. В этом случае формируется список ошибочных карт, который затем выводится в файл.
					$errCard=$eximdata->insertCardInTree(Arr::get($current, 'card'), $uid);
						$result['contactKeyForInsert']=count(Arr::get($current, 'card'));//количество карт для вставки.	
						$result['contactKeyInsertErr']=count($errCard);//количество карт для вставки.
						if($errCard){
							//готовлю файл с ошибками
										
							Session::instance()->set('e_mess', array('ok_mess'=>'При вставке данных произошли ошибки. Ошибки записаны в лог-файл приложения.'));
							
						} else {
							Session::instance()->set('ok_mess', array(__('Вставка данных из файла ":filename" прошла успешно.', array(':filename'=> $sourceFile,))));
					
						}
						
			
				}
			} else {
				
				//при добавлении организаций возникли ошибки.
				//удаляю все организации, у которых divcode содержи uid
				$eximdata->deleteOrgImportErr($uid);
				
			}
			
			$list_error[]= __('Добавлено организаций :orgCount<br>
								Добавлено контактов :contactCount<br>
								Добавлено идентификаторов без ошибки :contactKeyInsert<br>
								Не добавлено идентификаторов contactKeyInsertErr<br>
								',
						array(
							'orgCount'=>$result['orgCount'],
							'contactCount'=>$result['contactCount'],
							'contactKeyInsertErr'=>$result['contactKeyInsertErr'],
							'contactKeyInsert'=>$result['contactKeyForInsert']-$result['contactKeyInsertErr'],
							));
							
			
			Session::instance()->set('result_mess', $list_error);
			
			$this->redirect('/eximdata');
	}
	
	public function action_exportFull()
	{
		$id=$this->request->param('id');
			
		$file='file2_full.csv';	
		$id_org=Validation::factory(array('id_org'=>$id));
		$id_org->rule('id_org', 'digit')
				->rule('id_org', 'not_empty')
				->rule('id_org', 'Model_eximdata::unique_org');
		
		if($id_org->check())
			{
				$nameOrg=Model::factory('eximdata')->getFileNameFromIdOrg($id);// получил название организации как имя файла
				$file = preg_replace("([[:punct:] ])", '_', $nameOrg).'.csv';
				$list=Model::factory('eximdata')->export(Arr::get($id_org, 'id_org'));// получил список данных о сотрудниках для сохранения.
				//сохранение промежуточного файла
				$fp = fopen($file, 'w');
				
				foreach ($list as $fields) {
					fputcsv($fp, $fields, ';', '"');
				}
				if(!fclose($fp))
				{
					Session::instance()->set('err_mess', array('ok_mess'=>'Не могу сохранить файл '.$file));
				} 					

		
		
		//$content = Model::Factory('Log')->send_file($file);
		$content = Model::Factory('eximdata')->send_file($file);
		
		$this->redirect('/eximdata');
				
			} else {
				Session::instance()->set('e_mess', $id_org->errors('eximdata'));
				//$this->template->content = $content;
				$this->redirect('/eximdata');
				
			}	
		
		exit;
	}
	
	public function action_import()
	{
			$id_org=Arr::get($_POST, 'id_org1');
			$data=array();
			$list=array();
			$sourceFile = Arr::get($_FILES[$this->cacheFileName], 'name');//сохраняю имя файла для последующих комментариев
			
			$filename = $this->_uploadFile('csv');//загружаю файл типа csv
			
			$countrow=1;
			//чтение данных из файла и преобразование их в массив			
				
				if (($fp = fopen($filename, "r")) !== FALSE) {
					while (($data = fgetcsv($fp, 0, ";")) !== FALSE) {
						$list[] = $data;
						
					}
					fclose($fp);
				} else {
					
					echo Debug::vars('364');exit;
				}
	//	echo Debug::vars('407',$data, $list);exit;	
			//валидация полученных данных. Цель валидации - убедиться, что номеро карт нет в базе данных.
		$list_error=array();
			foreach($list as $key=>$value)
			{
			$countrow++;
			//echo Debug::vars('374', $value);//exit;
				$data=Validation::factory($value);
				$data->rule(0, 'digit')
					->rule(0, 'not_empty')
					->rule(1, 'max_length', array(':value', $this->nameLen))
					->rule(2, 'max_length', array(':value', $this->surNameLen))
					->rule(3, 'max_length', array(':value', $this->patronymicLen))
					->rule(4, 'max_length', array(':value', $this->noteLen))
					
					//->rule(5, 'regex', array(':value', '/^[A-F\d]{10}+$/')) // https://regex101.com/
					//->rule(5, 'Model_eximdata::unique_card') //проверка идентификатора на уникальность.
					->rule(5, 'not_empty')
					->rule(5, array('Model_eximdata', 'unique_card'), array(':value', Arr::get($value, 6)))
					->rule(6, 'not_empty')
					->rule(6, 'regex', array(':value', '/^[1-5]{1}+$/')) //^[1-5]{1}+$ https://regex101.com/
					;
				$keyTypeList=$this->_getCardtype();//получил список типов карт.
				
				//echo Debug::vars('427', $data);exit;
				if($data->check())//если карты нет в БД, то добавляем ее в базу данных
				{
					
					$keyNameList=array('id', 'name', 'surname', 'patronymic', 'note', 'key', 'type');
					$list2[]=array_combine($keyNameList, $value);
			
			
				
				} else {
					 
					$fioo = Model::factory('eximdata')->getInforForCard(Arr::get($data, 5));

					
						$list_error[]= __('481 err Ошибка в строке :countrow исходных данных :errstring. Ошибка :errMess',
						array(
							':errstring'=>iconv('windows-1251','UTF-8',implode(",", $value)),
							':errMess'=>implode(",", $data->errors('eximdata')),
							':countrow'=>$countrow,							
							));
							Kohana::$log->add(Log::ERROR, '440 ' . Debug::vars($list_error));
				}
			}
			//echo Debug::vars('442',$list_error );exit;
			$var415=count($list_error);
			if($var415>0) //есть ошибки в исходных данных. Импорт прекращается.
			{
				
				$list_error[]= __('481 err Выявлены ошибки в исходном файле в :count строк(-ах). Импорт невозможен.',
						array(
						':count'=>$var415,
							
							));
				Session::instance()->set('result_mess', $list_error);
				$this->redirect('/eximdata');
			} 
			
			//проверка данных в массиве list пройдена успешно, теперь можно вставлять данные в организацию
			$keyNameList=array('id', 'name', 'name2', 'name3', 'note', 'key', 'type');
			//echo Debug::vars('452 все в порядке, вставляю данные', $list);//exit;
			//echo Debug::vars('452 все в порядке, вставляю данные', $list2);exit;
		//	echo Debug::vars('453 все в порядке, вставляю данные', array_combine($keyNameList, $list));exit;
			$addIdPep=array();//список уже вставленных контактов в формате
			//'id из файла' =>id_pep 
			foreach($list2 as $key2=>$value2)
			{
				//echo Debug::vars('463',iconv('windows-1251','UTF-8', implode(",",$value2)), $addIdPep);exit;
				$insertIdPep=Arr::get($addIdPep, Arr::get($value2, 'id'));
				//echo Debug::vars('465', $insertIdPep);exit;
				if(!$insertIdPep)
				{	//если не существует ;insertIdPep - значит, этот контакт еще не вставляли

						$insertPeople=Model::factory('eximdata')->insertPeople($value2, $id_org);//получил ID_PEP вставленного пипла
						$addIdPep[Arr::get($value2, 'id')]=$insertPeople;//добавил в массив id_pep для вновь вставленного контакта
						
						if($insertPeople>0)//если не нуль, т.е. вставка прошла успешно, то 
						{
						//	echo Debug::vars('445',$insertPeople, $value2, Arr::get($value2, 'key'));exit;
							$fioo = Model::factory('eximdata')->getInforForCard(Arr::get($value2, 'key'));

						$result_mess[]= __('377 ok Карта :card пользователя :f :i :o тип :cardType зарегистрирована успешно в организацию :orgName.',
							array(
								':ffrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'SURNAME')),
								':ifrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'NAME')),
								':ofrom'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'PATRONYMIC')),
								':orgName'=>iconv('windows-1251','UTF-8',Arr::get($fioo, 'ORGNAME')),
								':f'=>iconv('windows-1251','UTF-8',Arr::get($value, 1)),
								':i'=>iconv('windows-1251','UTF-8',Arr::get($value, 2)),
								':o'=>iconv('windows-1251','UTF-8',Arr::get($value, 3)),
								':card'=>Arr::get($value, 5),
								':cardType'=>Arr::get($keyTypeList, Arr::get($value, 6)),
								));
						} else {
							
						}
					
						
						
				} else {//а если уже есть такой контакто, то просто добавляю ему карту
					//echo Debug::vars('472');exit;
					Model::factory('eximdata')->addCard($value2, $insertIdPep);//получил ID_PEP вставленного пипла
				}
			}				
			
			
	//		echo Debug::vars('476', $addIdPep);exit;
			
			
			Session::instance()->set('result_mess', $result_mess);
			

		// redirect to home page
		$this->redirect('/eximdata');
	}
	
		public function _getCardtype()
		{
			
			$sql='select c.id, c.name from cardtype c';
			
			$query=DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->as_array()
			;
			
			foreach($query as $key=>$value)
			{
				$list[Arr::get($value, 'ID')]=iconv('windows-1251','UTF-8' ,Arr::get($value, 'NAME'));
				
			}
			return $list;
		}
	
	/**30.11.2025 Загрузка файла с веб-формы
	* @param typeFile csv, json
	*/
	public function _uploadFile($typeFile)
	{
			if (!isset($_FILES['dataimport'])) {
				Session::instance()->set('e_mess', array('file_required' => 'File is required'));
				$this->redirect('/eximdata');
				return false;
			}
			
			$validation = Validation::factory($_FILES)
				->rule('dataimport', 'Upload::not_empty')
				->rule('dataimport', 'Upload::valid')
				->rule('dataimport', 'Upload::size', array(':value', '3M'))
				->rule('dataimport', 'Upload::type', array(':value', array($typeFile)));
			
			if (!$validation->check()) {
				Session::instance()->set('e_mess', $validation->errors('eximdata'));
				$this->redirect('/eximdata');
				return false;
			}
			
			try {
				$saved = Upload::save($_FILES['dataimport'], $this->cacheFileName, $this->cache_dir);
				if ($saved) {
					return $this->cache_dir . DIRECTORY_SEPARATOR . $this->cacheFileName;
				}
			} catch (Exception $e) {
				Kohana::$log->add(Log::ERROR, 'File upload error: ' . $e->getMessage());
				Session::instance()->set('e_mess', array('upload_error' => 'File upload failed'));
			}
			
			$this->redirect('/eximdata');
			return false;
	}
	
	
	private function _getFieldLen($nameField)
	{
		$sql=__('SELECT 
				f.rdb$field_length as byte_length as len,
				f.rdb$character_length as char_length
			FROM rdb$relation_fields rf
			JOIN rdb$fields f ON rf.rdb$field_source = f.rdb$field_name
			WHERE rf.rdb$relation_name = \'PEOPLE\' 
			  AND rf.rdb$field_name = \':name\'', array(':name'=>$nameField));
		
		
		$sql=__('SELECT 
				f.rdb$field_length as byte_length 				
			FROM rdb$relation_fields rf
			JOIN rdb$fields f ON rf.rdb$field_source = f.rdb$field_name
			WHERE rf.rdb$relation_name = \'PEOPLE\' 
			  AND rf.rdb$field_name = \':name\'', array(':name'=>$nameField));
			  
			  
			return DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'))
			->get('BYTE_LENGTH');
			
		
	}
}

