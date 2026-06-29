<style>
        .custom-bg-1 { background-color: #e8f4f8; padding: 10px; margin: 2px 0; }
        .custom-bg-2 { background-color: #fff0f0; padding: 10px; margin: 2px 0; }
        .custom-bg-3 { background-color: #f0fff0; padding: 10px; margin: 2px 0; }
        .custom-bg-4 { background-color: #fff8e1; padding: 10px; margin: 2px 0; }
        .custom-bg-5 { background-color: #f3e5f5; padding: 10px; margin: 2px 0; }
        
        .padded-line { 
            padding: 12px 15px; 
            margin: 2px 0; 
            border-radius: 4px;
        }
        
        /* Стили для таблицы предпросмотра */
        #previewTable {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        #previewTable th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f0f0f0 !important;
            border: 1px solid #ddd;
            padding: 4px 8px;
        }
        #previewTable td {
            border: 1px solid #ddd;
            padding: 4px 8px;
            max-width: 120px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        #previewContainer {
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fafafa;
            padding: 10px;
            margin-top: 15px;
            max-height: 320px;
            overflow: auto;
        }
        #previewContainer .table {
            margin-bottom: 0;
        }
        .btn-secondary {
            display: inline-block;
            padding: 8px 20px;
            background: #6c757d;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .btn-primary {
            display: inline-block;
            padding: 8px 20px;
            background: #337ab7;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
        }
        .btn-primary:hover {
            background: #286090;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-top: 15px;
            flex-direction: column;
            align-items: flex-start;
        }
        .form-actions .buttons-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .org-info {
            background: #e8f4f8;
            padding: 10px 15px;
            border-radius: 4px;
            margin: 10px 0;
            border-left: 4px solid #337ab7;
        }
        .format-error-msg {
            color: #d9534f;
            font-size: 13px;
            margin-top: 8px;
            font-weight: bold;
            display: none;
        }
        .duplicate-warning {
            color: #d9534f;
            display: none;
        }
</style>
<?php
    
    //echo Debug::vars('16', Session::instance() );//exit;
    
    
    // Получить список всех модулей
$modules = Kohana::modules();

// Получить информацию о конкретном модуле
        $module_path = $modules['eximdata'];
        $module_info='n/a';
        if (is_file($module_path.'version.php')) {
            $module_info = include $module_path.'version.php';
            
        }

?>
    
    
<?php

        $e_mess=Validation::Factory(Session::instance()->as_array())
                ->rule('e_mess','is_array')
                ->rule('e_mess','not_empty')
                ;
        
        if($e_mess->check())
        {
    
            $param='Err message<br>';
            
            foreach(Arr::get($e_mess, 'e_mess') as $key=>$value)
            {
                $param.=$value.'<br>';
            }
            ?>
            <div id="my-alert" class="alert alert-danger alert-dismissible" role="alert">
                <?php 
                    echo $param;
                ?>
                
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            
        } else 
        {
            
            
        }
        Session::instance()->delete('e_mess');
        
        $ok_mess=Validation::Factory(Session::instance()->as_array())
                ->rule('ok_mess','is_array')
                ->rule('ok_mess','not_empty')
                ;
        
        if($ok_mess->check())
        {
    
            $param='Ok message<br>';
            
            foreach(Arr::get($ok_mess, 'ok_mess') as $key=>$value)
            {
                $param.=$value.'<br>';
            }
            ?>
            <div id="my-alert" class="alert alert-success alert-dismissible" role="access">
                <?php 
                    echo $param;
                ?>
                
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            
        } else 
        {
            
            
        }
        Session::instance()->delete('ok_mess');
        
        
        //вывод результата вставки 
        $result_mess=Validation::Factory(Session::instance()->as_array())
                ->rule('result_mess','is_array')
                ->rule('result_mess','not_empty')
                ;
        
        if($result_mess->check())
        {
    
            $param='Ok message<br>';
            
            foreach(Arr::get($result_mess, 'result_mess') as $key=>$value)
            {
                //$param.=$value.'<br>';
                
                if (strpos($value, 'err') == false) {
                    $class='custom-bg-3';
                } else {
                    $class='custom-bg-2';
                }
            
                $param.=__('<p class=":class">:mess.</p>', array(':class'=>$class, ':mess'=>$value));
            }
            ?>
            <div id="my-alert" class="alert alert-success alert-dismissible" role="access">
                <?php 
                    echo $param;
                    
                ?>
                
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php
            
        } else 
        {
            
            
        }
        Session::instance()->delete('result_mess');
        
        
?>

    
    <fieldset>
        <legend>Описание</legend>
     
       <div class="alert alert-success">
           <?php echo __('<abbr title=":about">Версия :info</abbr> ', 
            array(
                ':info'=> $module_info['version'],
                ':about'=> $module_info['abbr']));?>
           
           <br>
           <?php echo __('about_eximport');?>
        </div>
  </fieldset>
    

<fieldset>
    <legend>
        <a data-toggle="collapse" href="#csvFormatCollapse" role="button" aria-expanded="false" aria-controls="csvFormatCollapse" style="display: block; text-decoration: none; color: inherit;">
            Формат csv файла для импорта
            <span class="pull-right" style="font-size: 14px; font-weight: normal;">▼ показать</span>
        </a>
    </legend>
    
    <div class="collapse" id="csvFormatCollapse">
        <div class="alert alert-success">
            <?php echo __('Формат :about', 
                array(
                    ':info'=> $module_info['csv_format'],
                    ':about'=> $module_info['csv_format']));?>
            <br>
            <?php //echo __('about_eximport');?>
        </div>
    </div>
</fieldset>
    

    <fieldset>
        <legend>Предупреждение</legend>
    
             <div class="alert alert-danger">
                <?php echo __('warning_tree_import');?>
            </div>
  </fieldset>
  
    <div class="panel panel-primary">
      <div class="panel-heading">
        <h3 class="panel-title"><?echo __('Export/import people');?></h3>
      </div>
      <div class="panel-body">
        <?
        
        if(isset ($orgList)){
        ?>
                    <table class="table table-striped table-hover table-condensed">

        <tr>
            
            <th><?echo __('ID_ORG');?></th>
            <th><?echo __('ORG_NAME');?></th>
            <th><?echo __('ORG_CHILD');?></th>
            <th><?echo __('ORG_PEOPLE_COUNT');?></th>
            <th><?echo __('ToDo');?></th>
            <th><?echo __('ToDo2');?></th>
            
            
        </tr>
        <?
            $config_analyt_code=Kohana::$config->load('artonitcity_config')->analit_err;
            
            if(isset($orgList) and count($orgList))
            {
                foreach ($orgList as $key => $data)
                {
                    $class_text='text-success';
                    if(in_array(Arr::get($data, 'ANALIT'), $config_analyt_code)) $class_text='text-danger "font-weight-bold"';
                    
                    echo '<tr  class="'.$class_text.'">';
                        echo '<td>'.Arr::get($data, 'ID_ORG').'</td>';
                         if (Arr::get($data, 'COUNT') >0) 
                            {
                                echo '<td>'. HTML::anchor('eximdata/index/'.Arr::get($data, 'ID_ORG'),iconv('windows-1251', 'utf-8', Arr::get($data, 'NAME'))).'</td>';
                        } else {
                            echo '<td>'. iconv('windows-1251', 'utf-8', Arr::get($data, 'NAME')).'</td>';
                        };
                        echo '<td>'.Arr::get($data, 'COUNT').'</td>';
                        echo '<td>'.Arr::get($countPeopleInOrg, Arr::get($data, 'ID_ORG')).'</td>';
                        echo '<td>'.HTML::anchor('eximdata/export/'. Arr::get($data, 'ID_ORG'), __('Export_people'))
                                    .'<label class="btn btn-line dark btn-xs popup-contact" for="modalm-1" org-name="'.iconv('windows-1251', 'utf-8', Arr::get($data, 'NAME')).'" org_id="'.Arr::get($data, 'ID_ORG').'">Импорт</label>'
                                    
                                    
                        .'</td>';
                        echo '<td>'.HTML::anchor('eximdata/exportTree/'. Arr::get($data, 'ID_ORG'), __('exportTree'))
                                    .'<label class="btn btn-line dark btn-xs popup-contact" for="modalm-2" org-name="'.iconv('windows-1251', 'utf-8', Arr::get($data, 'NAME')).'" org_id2="'.Arr::get($data, 'ID_ORG').'">importTree</label>'
                                    
                                    
                        .'</td>';
                        
                    echo '</tr>';
                    
                }
            }   else {
                echo __('no_data');
            }
            ?>
            </table>
            <?
        } else {
            echo __('no data');
        }
            
?>            
        

            
        
      </div>
    </div>


<!-- МОДАЛЬНОЕ ОКНО ДЛЯ ИМПОРТА ПОЛЬЗОВАТЕЛЕЙ (с предпросмотром) -->
<div class="modalm">
    <input class="modalm-open" id="modalm-1" type="checkbox" hidden>
    <div class="modalm-wrap" aria-hidden="true" role="dialog">
        
        <div class="modalm-dialog" style="max-width: 800px;">
            <div class="modalm-header">
                <h2>Выберите файл для импорта пользователей</h2>
                <label class="btnm-close" for="modalm-1" aria-hidden="true">×</label>
            </div>
            <div class="modalm-body">
                <h3>Будет выполнена вставка пользователей в организацию</h3>
                
                <!-- БЛОК С НАЗВАНИЕМ ОРГАНИЗАЦИИ ДЛЯ modalm-1 -->
                <div class="org-info">
                    <strong>Организация:</strong> <span id="org-name-display-1" style="color: #337ab7;">не выбрана</span>
                    <span style="color: #666; font-size: 12px;">(ID: <span id="org-id-display-1">—</span>)</span>
                </div>
                
                <div class="row">
                    <div class="kartka">
                        <h4>Поддерживаемые форматы: CSV</h4>
                    </div>
                </div>
                
                <form id="importForm" action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="file" name="dataimport" id="csvFileInput" accept=".csv,.txt" required>
                    </div>
                    <input type="hidden" name="id_org1" id="id_org1">
                    
                    <!-- КОНТЕЙНЕР ДЛЯ ПРЕДПРОСМОТРА -->
                    <div id="previewContainer" style="display: none;">
                        <div style="background: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 300px; overflow: auto;">
                            <table id="previewTable" class="table table-striped table-bordered table-condensed" style="font-size: 12px; margin: 0; background: #fff;">
                                <thead id="previewHeader"></thead>
                                <tbody id="previewBody"></tbody>
                            </table>
                        </div>
                        <div style="margin-top: 10px; font-size: 12px; color: #666;">
                            Показано первых 10 строк из <span id="totalRows">0</span>.
                            <span class="duplicate-warning" id="duplicateWarning">
                                ⚠️ Найдены строки с одинаковым ID. Им будут присвоены дополнительные карты.
                            </span>
                        </div>
                        
                        <!-- БЛОК ДЛЯ ВЫВОДА ОШИБОК ВАЛИДАЦИИ -->
                        <div id="validationError" style="display: none;"></div>
                    </div>
                    
                    <div class="form-actions">
                        <div class="buttons-row">
                            <button type="submit" name="submit" value="Загрузить" class="btn-primary" id="importSubmitBtn" style="display: none;">Подтвердить импорт</button>
                            <button type="button" class="btn-secondary" onclick="resetFileInput()">Очистить</button>
                            <label for="modalm-1" class="btn-secondary">Отмена</label>
                        </div>
                        
                        <!-- КРАСНАЯ НАДПИСЬ ПРИ ОШИБКЕ -->
                        <div class="format-error-msg" id="formatErrorMsg">
                            ⚠️ См. документацию "Формат CSV файла для импорта" выше
                        </div>
                    </div>
                </form> 
            </div>
            <div class="modalm-footer">
                <h4>Экспорт-импорт данных о сотрудниках</h4>
            </div>
        </div>
    </div>
</div>


<!-- МОДАЛЬНОЕ ОКНО ДЛЯ ИМПОРТА ДЕРЕВА -->
<div class="modalm">
    <input class="modalm-open" id="modalm-2" type="checkbox" hidden>
    <div class="modalm-wrap" aria-hidden="true" role="dialog" aria-labelledby="modal-title-2" aria-describedby="modal-desc-2">
        
        <div class="modalm-dialog">
            <div class="modalm-header">
                <h2 id="modal-title-2">Выберите файл для импорта организаций и пользователей</h2>
                <label class="btnm-close" for="modalm-2" aria-hidden="true">×</label>
            </div>
            <div class="modalm-body">
                <h3 id="modal-desc-2">Будет выполнена вставка организаций и пользователей в выбранную организацию</h3>
                
                <!-- БЛОК С НАЗВАНИЕМ ОРГАНИЗАЦИИ ДЛЯ modalm-2 -->
                <div class="org-info">
                    <strong>Организация:</strong> <span id="org-name-display-2" style="color: #337ab7;">не выбрана</span>
                    <span style="color: #666; font-size: 12px;">(ID: <span id="org-id-display-2">—</span>)</span>
                </div>
                
                <div class="row">
                    <div class="kartka">
                        <p>Поддерживаемые форматы: json</p>
                        <p class="text-small">Файл должен содержать структуру организаций и соответствующих пользователей</p>
                    </div>
                </div>
                
                <form action="eximdata/importTree" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="dataimport-file">Выберите файл:</label>
                        <input type="file" name="dataimport" id="dataimport-file" accept=".json" required>
                    </div>
                    <input type="hidden" name="id_org2" id="id_org2">
                    <div class="form-actions">
                        <div class="buttons-row">
                            <input type="submit" name="submit" value="Импортировать" class="btn-primary">
                            <label for="modalm-2" class="btn-secondary">Отмена</label>
                        </div>
                    </div>
                </form> 
            </div>
            <div class="modalm-footer">
                <h4>Импорт данных организаций и сотрудников</h4>
            </div>
        </div>
    </div>
</div>
    
    
<?php
// передача данных в модальное окно взято отсюда: https://ru.stackoverflow.com/questions/491392/%D0%9A%D0%B0%D0%BA-%D0%BF%D1%80%D0%B0%D0%B2%D0%B8%D0%BB%D1%8C%D0%BD%D0%BE-%D0%B2%D1%8B%D0%B2%D0%B5%D1%81%D1%82%D0%B8-data-%D0%B0%D1%82%D1%80%D0%B8%D0%B1%D1%83%D1%82%D1%8B-%D0%B2-%D0%BC%D0%BE%D0%B4%D0%B0%D0%BB%D1%8C%D0%BD%D0%BE%D0%BC-%D0%BE%D0%BA%D0%BD%D0%B5-bootstrap
?>    
 <script>
 //https://learn.javascript.ru/function-object
$(function() {
    
     $(".popup-contact").click(
       function() {
         var orgName = $(this).attr('org-name');
         var orgId = $(this).attr('org_id');
         var orgId2 = $(this).attr('org_id2');
         
         // Сбрасываем предпросмотр при открытии
         resetFileInput();
         
         // Для модального окна modalm-1 (импорт пользователей)
         if ($(this).attr('for') == 'modalm-1') {
             $("#org-name-display-1").text(orgName);
             $("#org-id-display-1").text(orgId);
             document.getElementById("id_org1").value = orgId;
         }
         
         // Для модального окна modalm-2 (импорт дерева)
         if ($(this).attr('for') == 'modalm-2') {
             $("#org-name-display-2").text(orgName);
             $("#org-id-display-2").text(orgId2);
             document.getElementById("id_org2").value = orgId2;
         }
       });
    
    // Обработчик выбора файла для предпросмотра
    document.getElementById('csvFileInput').addEventListener('change', function(e) {
        var file = e.target.files[0];
        if (!file) {
            resetFileInput();
            return;
        }
        
        var reader = new FileReader();
        reader.onload = function(e) {
            var content = e.target.result;
            var lines = content.split('\n');
            var data = [];
            var duplicates = {};
            var validationErrors = [];
            var hasErrors = false;
            
            // Парсим CSV
            for (var i = 0; i < lines.length; i++) {
                if (lines[i].trim() === '') continue;
                var row = lines[i].split(';');
                // Очищаем кавычки если есть
                row = row.map(function(cell) {
                    return cell.replace(/^"|"$/g, '').trim();
                });
                data.push(row);
                
                // Проверяем дубликаты по ID (первая колонка)
                if (row[0]) {
                    if (duplicates[row[0]]) {
                        duplicates[row[0]]++;
                    } else {
                        duplicates[row[0]] = 1;
                    }
                }
                
                // ===== ВАЛИДАЦИЯ СТРОКИ =====
                var rowErrors = [];
                var rowNum = i + 1;
                
                // 1. ID - обязательно
                if (!row[0] || row[0] === '') {
                    rowErrors.push('ID обязательно');
                } else if (!/^\d+$/.test(row[0])) {
                    rowErrors.push('ID должен быть числом');
                }
                
                // 2. Фамилия - обязательно
                if (!row[1] || row[1] === '') {
                    rowErrors.push('Фамилия обязательна');
                }
                
                // 3. Имя - необязательно
                // 4. Отчество - необязательно
                // 5. Примечание - необязательно
                
                // 6. Номер карты - обязательно, только цифры и буквы ABCDEF, не более 10 символов
                if (!row[5] || row[5] === '') {
                    rowErrors.push('Номер карты обязателен');
                } else if (!/^[A-Fa-f0-9]{1,10}$/.test(row[5])) {
                    rowErrors.push('Номер карты должен содержать только цифры и буквы A-F (не более 10 символов)');
                }
                
                // 7. Тип карты - обязательно (1-5)
                if (!row[6] || row[6] === '') {
                    rowErrors.push('Тип карты обязателен');
                } else if (!/^[1-5]$/.test(row[6])) {
                    rowErrors.push('Тип карты должен быть числом от 1 до 5');
                }
                
                // Если есть ошибки в строке
                if (rowErrors.length > 0) {
                    hasErrors = true;
                    validationErrors.push({
                        rowNum: rowNum,
                        rowData: row,
                        errors: rowErrors
                    });
                }
            }
            
            // Проверяем наличие дубликатов
            var hasDuplicates = false;
            for (var id in duplicates) {
                if (duplicates[id] > 1) {
                    hasDuplicates = true;
                    break;
                }
            }
            
            // Показываем таблицу
            var previewContainer = document.getElementById('previewContainer');
            previewContainer.style.display = 'block';
            document.getElementById('totalRows').textContent = data.length;
            
            // Показываем предупреждение о дубликатах
            var warningEl = document.getElementById('duplicateWarning');
            if (hasDuplicates) {
                warningEl.style.display = 'inline';
            } else {
                warningEl.style.display = 'none';
            }
            
            // ===== СООБЩЕНИЕ ОБ ОШИБКАХ ВАЛИДАЦИИ =====
            var validationErrorEl = document.getElementById('validationError');
            var importSubmitBtn = document.getElementById('importSubmitBtn');
            var formatErrorMsg = document.getElementById('formatErrorMsg');
            
            if (hasErrors) {
                // Строим сообщение об ошибке
                var errorMsg = '<div style="color: #d9534f; background: #f2dede; padding: 10px; border-radius: 4px; border: 1px solid #ebccd1; margin-top: 10px;">';
                errorMsg += '<strong>⚠️ Ошибка формата данных!</strong><br>';
                errorMsg += 'Найдены ошибки в ' + validationErrors.length + ' строке(ах):<br><br>';
                
                // Показываем первые 5 ошибок
                var showErrors = validationErrors.slice(0, 5);
                for (var i = 0; i < showErrors.length; i++) {
                    var err = showErrors[i];
                    errorMsg += '<div style="background: #fff; padding: 5px 10px; margin: 3px 0; border-radius: 3px; font-size: 13px;">';
                    errorMsg += '<strong>Строка ' + err.rowNum + ':</strong> ';
                    errorMsg += err.rowData.join('; ') + '<br>';
                    errorMsg += '<span style="color: #d9534f;">→ ' + err.errors.join('; ') + '</span>';
                    errorMsg += '</div>';
                }
                
                if (validationErrors.length > 5) {
                    errorMsg += '<div style="font-size: 13px; color: #999; margin-top: 5px;">... и еще ' + (validationErrors.length - 5) + ' строк с ошибками</div>';
                }
                
                errorMsg += '</div>';
                validationErrorEl.innerHTML = errorMsg;
                validationErrorEl.style.display = 'block';
                
                // Показываем красную надпись под кнопкой
                formatErrorMsg.style.display = 'block';
                
                // Блокируем кнопку импорта
                importSubmitBtn.disabled = true;
                importSubmitBtn.style.opacity = '0.5';
                importSubmitBtn.style.cursor = 'not-allowed';
                importSubmitBtn.style.display = 'inline-block';
                importSubmitBtn.title = 'Исправьте ошибки в файле перед импортом';
            } else {
                // Ошибок нет - скрываем сообщение и активируем кнопку
                validationErrorEl.style.display = 'none';
                formatErrorMsg.style.display = 'none';
                importSubmitBtn.disabled = false;
                importSubmitBtn.style.opacity = '1';
                importSubmitBtn.style.cursor = 'pointer';
                importSubmitBtn.style.display = 'inline-block';
                importSubmitBtn.title = '';
            }
            
            // Строим таблицу
            var header = document.getElementById('previewHeader');
            var body = document.getElementById('previewBody');
            
            // Заголовки
            var headers = ['ID', 'Фамилия', 'Имя', 'Отчество', 'Примечание', 'Номер карты', 'Тип карты'];
            var headerRow = '<tr>';
            for (var i = 0; i < headers.length; i++) {
                headerRow += '<th>' + headers[i] + '</th>';
            }
            headerRow += '</tr>';
            header.innerHTML = headerRow;
            
            // Данные (первые 10 строк)
            var bodyHtml = '';
            var maxRows = Math.min(10, data.length);
            for (var i = 0; i < maxRows; i++) {
                var row = data[i];
                var rowClass = '';
                var rowHasError = false;
                
                // Проверяем, есть ли ошибки в этой строке
                for (var j = 0; j < validationErrors.length; j++) {
                    if (validationErrors[j].rowNum === i + 1) {
                        rowHasError = true;
                        break;
                    }
                }
                
                // Подсвечиваем строки с ошибками красным
                if (rowHasError) {
                    rowClass = 'style="background: #f2dede;"';
                } else if (duplicates[row[0]] && duplicates[row[0]] > 1) {
                    rowClass = 'style="background: #fff3cd;"';
                }
                
                bodyHtml += '<tr ' + rowClass + '>';
                for (var j = 0; j < 7; j++) {
                    var cellValue = (j < row.length) ? row[j] : '';
                    bodyHtml += '<td>' + (cellValue || '&nbsp;') + '</td>';
                }
                bodyHtml += '</tr>';
            }
            body.innerHTML = bodyHtml;
        };
        reader.readAsText(file, 'Windows-1251'); // Для русских букв
    });
    
    // Функция сброса
    window.resetFileInput = function() {
        document.getElementById('csvFileInput').value = '';
        document.getElementById('previewContainer').style.display = 'none';
        document.getElementById('importSubmitBtn').style.display = 'none';
        document.getElementById('importSubmitBtn').disabled = false;
        document.getElementById('importSubmitBtn').style.opacity = '1';
        document.getElementById('importSubmitBtn').style.cursor = 'pointer';
        document.getElementById('duplicateWarning').style.display = 'none';
        document.getElementById('validationError').style.display = 'none';
        document.getElementById('validationError').innerHTML = '';
        document.getElementById('formatErrorMsg').style.display = 'none';
    };
});
</script>