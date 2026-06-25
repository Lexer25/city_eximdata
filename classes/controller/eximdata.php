<?php defined('SYSPATH') or die('No direct script access.');

class Controller_eximdata extends Controller_Template {

    public $template = 'template';
    private $cache_dir;
    private $cacheFileName = 'dataimport';

    public function before() {
        parent::before();
        $this->cache_dir = APPPATH . 'cache/';
    }

    public function action_index() {
		
        $id = (int)$this->request->param('id', 1);
        $model = Model::factory('eximdata');

        $content = View::factory('eximpdata', array(
            'orgList' => $model->getChild($id),
            'countChild' => $model->countChild($id),
            'countPeopleInOrg' => $model->countPeopleInOrg(),
            'version_info' => $this->getModuleVersion(),
        ));

        $this->template->content = $content;
    }

    public function action_editOrg() {
        $id = $this->request->param('id');
        $id_org = Validation::factory(array('id_org' => $id));
        $id_org->rule('id_org', 'digit')
               ->rule('id_org', 'not_empty')
               ->rule('id_org', 'Model_eximdata::unique_org');
        
        if ($id_org->check()) {
            $nameOrg = Model::factory('eximdata')->getFileNameFromIdOrg($id);
            $list = Model::factory('eximdata')->export(Arr::get($id_org, 'id_org'));
        } else {
            Session::instance()->set('e_mess', $id_org->errors('eximdata'));
            $this->redirect('/eximdata');
            return;
        }

        $content = View::factory('org/view', array(
            'nameOrg' => $nameOrg,
            'list' => $list,
            'id_org' => Arr::get($id_org, 'id_org')
        ));
        $this->template->content = $content;
    }

    public function action_executor() {
        $post = Validation::factory($_POST);
        $post->rule('timeStart', 'regex', array(':value', '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'))
             ->rule('timeEnd', 'regex', array(':value', '/^([0-1]?[0-9]|2[0-3]):[0-5][0-9]$/'))
             ->rule('updateWorkTime', 'not_empty')
             ->rule('id_org', 'not_empty')
             ->rule('id_org', 'digit');

        if ($post->check()) {
            Model::factory('eximdata')->setWorkTime(
                Arr::get($post, 'timeStart'),
                Arr::get($post, 'timeEnd'),
                Arr::get($post, 'id_org')
            );
            Session::instance()->set('ok_mess', array('Обновление времен начала и завершения рабочего дня выполнено успешно.'));
            $this->redirect('/eximdata/editOrg/' . Arr::get($post, 'id_org'));
        } else {
            Session::instance()->set('e_mess', $post->errors('eximdata'));
            $this->redirect('/eximdata');
        }
    }

    // === ЭКСПОРТ СОТРУДНИКОВ (CSV) ===
    public function action_export() {
        $id = (int)$this->request->param('id');

        try {
            $service = new EximdataExport();  // Изменено!
            $data = $service->exportPeople($id);

            if (empty($data)) {
                throw new Exception('Нет данных для экспорта.');
            }

            $nameOrg = Model::factory('eximdata')->getFileNameFromIdOrg($id);
            $filename = preg_replace("([[:punct:] ])", '_', $nameOrg) . '.csv';

            $fp = fopen('php://temp', 'w');
            foreach ($data as $row) {
                fputcsv($fp, $row, ';', '"');
            }
            rewind($fp);
            $csvContent = stream_get_contents($fp);
            fclose($fp);

            $this->sendFile($csvContent, $filename, 'text/csv');

        } catch (Exception $e) {
            Session::instance()->set('e_mess', array($e->getMessage()));
            $this->redirect('/eximdata');
        }
    }

    // === ЭКСПОРТ ДЕРЕВА (JSON) ===
    public function action_exportTree() {
        $id = (int)$this->request->param('id');

        try {
            $service = new EximdataExport();  // Изменено!
            $data = $service->exportTree($id);

            if (empty($data['org'])) {
                throw new Exception('Нет данных для экспорта.');
            }

            $nameOrg = Model::factory('eximdata')->getFileNameFromIdOrg($id);
            $filename = preg_replace("([[:punct:] ])", '_', $nameOrg) . '.json';
            $jsonContent = serialize($data);

            $this->sendFile($jsonContent, $filename, 'application/octet-stream');

        } catch (Exception $e) {
            Session::instance()->set('e_mess', array($e->getMessage()));
            $this->redirect('/eximdata');
        }
    }

    // === ИМПОРТ ИЗ CSV ===
    public function action_import() {
		//echo Debug::vars('131');exit;
        $id_org = (int)$this->request->post('id_org1');

        if ($id_org <= 0) {
            Session::instance()->set('e_mess', array('Не выбрана организация для импорта.'));
            $this->redirect('/eximdata');
            return;
        }

        $uploadedFile = $this->uploadFile('csv');
        if ($uploadedFile === false) {
            $this->redirect('/eximdata');
            return;
        }

        $service = new EximdataImport();  // Изменено!
        $result = $service->importFromCsv($uploadedFile, $id_org);

        if ($result->result === 0) {
            Session::instance()->set('ok_mess', array(
                "Импорт завершен успешно. Добавлено записей: {$result->countResult}."
            ));
        } else {
            Session::instance()->set('e_mess', array($result->errorMessage));
        }

        if (file_exists($uploadedFile)) {
            unlink($uploadedFile);
        }

        $this->redirect('/eximdata');
    }

    // === ИМПОРТ ДЕРЕВА ===
    public function action_importTree() {
        $id_org = (int)$this->request->post('id_org2');

        if ($id_org <= 0) {
            Session::instance()->set('e_mess', array('Не выбрана организация для импорта.'));
            $this->redirect('/eximdata');
            return;
        }

        $uploadedFile = $this->uploadFile('json');
        if ($uploadedFile === false) {
            $this->redirect('/eximdata');
            return;
        }

        $service = new EximdataImport();  // Изменено!
        $result = $service->importTree($uploadedFile, $id_org);

        if ($result->result === 0) {
            Session::instance()->set('ok_mess', array($result->errorMessage));
        } else {
            Session::instance()->set('e_mess', array($result->errorMessage));
        }

        if (file_exists($uploadedFile)) {
            unlink($uploadedFile);
        }

        $this->redirect('/eximdata');
    }

    // === ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ===

private function uploadFile($type) {
    Kohana::$log->add(Log::DEBUG, 'uploadFile: Начало. type=' . $type);
    
    if (!isset($_FILES['dataimport'])) {
        Kohana::$log->add(Log::ERROR, 'uploadFile: Файл не загружен (нет в $_FILES)');
        Session::instance()->set('e_mess', array('Файл не был загружен.'));
        return false;
    }

    Kohana::$log->add(Log::DEBUG, 'uploadFile: $_FILES[dataimport]=' . print_r($_FILES['dataimport'], true));

    $validation = Validation::factory($_FILES)
        ->rule('dataimport', 'Upload::not_empty')
        ->rule('dataimport', 'Upload::valid')
        ->rule('dataimport', 'Upload::size', array(':value', '3M'))
        ->rule('dataimport', 'Upload::type', array(':value', array($type)));

    if (!$validation->check()) {
        $errors = $validation->errors('eximdata');
        Kohana::$log->add(Log::ERROR, 'uploadFile: Ошибки валидации: ' . print_r($errors, true));
        Session::instance()->set('e_mess', $errors);
        return false;
    }

    try {
        Kohana::$log->add(Log::DEBUG, 'uploadFile: Сохранение файла в ' . $this->cache_dir);
        $saved = Upload::save($_FILES['dataimport'], $this->cacheFileName, $this->cache_dir);
        
        if ($saved) {
            $fullPath = $this->cache_dir . DIRECTORY_SEPARATOR . $this->cacheFileName;
            Kohana::$log->add(Log::DEBUG, 'uploadFile: Файл сохранен: ' . $fullPath);
            return $fullPath;
        } else {
            Kohana::$log->add(Log::ERROR, 'uploadFile: Upload::save вернул false');
        }
    } catch (Exception $e) {
        Kohana::$log->add(Log::ERROR, 'uploadFile: Ошибка загрузки файла: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
        Session::instance()->set('e_mess', array('Не удалось сохранить загруженный файл.'));
    }

    return false;
}

    private function sendFile($content, $filename, $mimeType) {
        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($content));
        echo $content;
        exit;
    }

    private function getModuleVersion() {
        $modules = Kohana::modules();
        $module_path = isset($modules['eximdata']) ? $modules['eximdata'] : null;
        
        if ($module_path && is_file($module_path . 'version.php')) {
            return include $module_path . 'version.php';
        }
        
        return array(
            'version' => 'n/a',
            'abbr' => 'Информация отсутствует.'
        );
    }
}
