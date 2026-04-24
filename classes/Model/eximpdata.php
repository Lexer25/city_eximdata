<?php
	//echo __('eximpdata');
?>

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
			//echo Debug::vars('133', $countPeopleInOrg);
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
				<label class="btnm-close" for="modalm-1" aria-hidden="true">x</label>
			</div>
			<div class="modalm-body">
			<h2>Будет выполнена вставка пользователей в организацию</h2>
			
				<div class="row">
					<div class="kartka">
					  <h1></h1>
					  </div>
			</div>
			
				<form action="eximdata/upload" method="post" enctype="multipart/form-data">
					<input type="file" name="csv">
					<input type="hidden" name="id_org1" id="id_org1" >
			<input type="submit" name="submit" value="Upload">
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
	<div class="modalm-wrap" aria-hidden="true" role="dialog">
		
		<div class="modalm-dialog">
			<div class="modalm-header">
				<h2>Выберите файл для импорта организаций и пользователей</h2>
				<label class="btnm-close" for="modalm-2" aria-hidden="true">x</label>
			</div>
			<div class="modalm-body">
			<h2>Будет выполнена вставка организаций и пользователей в выбранную организацию</h2>
			
				<div class="row">
					<div class="kartka">
					  <h1></h1>88
					  </div>
			</div>
			
				<form action="eximdata/importTree" method="post" enctype="multipart/form-data">
					<input type="file" name="dataimport">
					<input type="hidden" name="id_org2" id="id_org2" >
			<input type="submit" name="submit" value="Upload">
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



