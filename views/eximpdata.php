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
			}	else {
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


<div class="modalm">
    <input class="modalm-open" id="modalm-1" type="checkbox" hidden>
    <div class="modalm-wrap" aria-hidden="true" role="dialog">
        
        <div class="modalm-dialog">
            <div class="modalm-header">
                <h2>Выберите файл для импорта пользователей</h2>
                <label class="btnm-close" for="modalm-1" aria-hidden="true">×</label>
            </div>
            <div class="modalm-body">
                <h3>Будет выполнена вставка пользователей в организацию</h3>
                
                <div class="row">
                    <div class="kartka">
                        <h4>Поддерживаемые форматы: CSV</h4>
                    </div>
                </div>
                
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <input type="file" name="dataimport" accept=".csv,.txt" required>
                    </div>
                    <input type="hidden" name="id_org1" id="id_org1">
                    <div class="form-actions">
                        <input type="submit" name="submit" value="Загрузить" class="btn-primary">
                        <label for="modalm-1" class="btn-secondary">Отмена</label>
                    </div>
                </form> 
            </div>
            <div class="modalm-footer">
                <h4>Экспорт-импорт данных о сотрудниках</h4>
            </div>
        </div>
    </div>
</div>



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
                        <input type="submit" name="submit" value="Импортировать" class="btn-primary">
                        <label for="modalm-2" class="btn-secondary">Отмена</label>
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
	
     $(".btn").click(
       function() {
         var bname = $(this).attr('org-name');
         var bprice = $(this).attr('org_id');

	
         $(".kartka h1").text(bname);
         $(".kartka p").html(bprice);
		 document.getElementById("id_org1").value = $(this).attr('org_id');
		 document.getElementById("id_org2").value = $(this).attr('org_id2');
       });
	

   });
 
 
</script>



