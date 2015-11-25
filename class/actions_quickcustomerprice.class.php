<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_quickcustomerprice.class.php
 * \ingroup quickcustomerprice
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionsquickcustomerprice
 */
class Actionsquickcustomerprice
{
	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var array Errors
	 */
	public $errors = array();

	/**
	 * Constructor
	 */
	public function __construct()
	{
	}

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array()         $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    &$object        The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          &$action        Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	function formObjectOptions($parameters, &$object, &$action, $hookmanager)
	{
		if (in_array('propalcard', explode(':', $parameters['context'])))
		{
		  
		  	?>
		  	<script type="text/javascript">
		  		$(document).ready(function() {
			  		$('table#tablelines tr[id]').find('td.linecoluht,td.linecoldiscount,td.linecolqty').each(function(i,item) {
			  			value = $(item).html();
			  			lineid = $(item).closest('tr').attr('id').substr(4);
			  			
			  			if($(item).hasClass('linecoldiscount')) {
			  				col='remise_percent';
			  			}
			  			else if($(item).hasClass('linecolqty')) {
			  				col='qty';
			  			}
			  			else {
			  				col = 'price';
			  			}
			  			
			  			$a = $('<a class="blue" />');
			  			$a.attr('href', "javascript:;");
			  			$a.attr('value', value);
			  			$a.attr('col', col);
			  			$a.attr('lineid', lineid);
			  			$a.attr('objectid', '<?php echo $object->id; ?>');
			  			$a.attr('objectelement', '<?php echo $object->element; ?>');
			  			
			  			if(value == '' || value=='&nbsp;') $(item).html('...');
			  			
			  			$(item).wrapInner($a);
			  			
			  			$(item).append('<input type="text" class="flat qcp" name="qcp-price" style="display:none;" size="8" />');
			  			
			  			$(item).unbind().click(function() {
			  				var $link = $(this).find('a');
			  				var $input = $(this).find('input.qcp');
			  				
			  				if($link.is(':visible')) {
				  				$link.hide();
				  				$input.show();
				  				$input.val($link.attr('value').trim());
				  				$input.focus();
				  				$input.select();
			  				}
			  				
			  				$input.unbind();
			  				$input.keypress(function (evt) {
								//Deterime where our character code is coming from within the event
								var charCode = evt.charCode || evt.keyCode;
								if (charCode  == 13) { //Enter key's keycode
									$input.blur();
								
									return false;
								}
							});
			  				
			  				$input.blur(function() {
			  					var value = $(this).val();
			  					var col = $link.attr('col');
			  					var lineid = $link.attr('lineid');
				  				var objectid = $link.attr('objectid');
				  				var objectelement = $link.attr('objectelement');
				  				$link.show();
				  				$link.html('...');
			  					$input.hide();
			  					
			  					$.ajax({
			  						url:"<?php echo dol_buildpath('/quickcustomerprice/script/interface.php',1) ?>"
			  						,data: {
			  							put:'price'
			  							,value:value
			  							,column:col
			  							,lineid:lineid
			  							,objectid:objectid
			  							,objectelement:objectelement
			  						}
			  						,dataType:'json'
			  					}).done(function(data) {
			  						
			  						$('tr[id=row-'+lineid+'] td.liencolht').html(data.total_ht);
			  						$('tr[id=row-'+lineid+'] td.linecoldiscount a').html(data.remise+'%');
			  						$('tr[id=row-'+lineid+'] td.linecolqty a').html(data.qty);
			  						$('tr[id=row-'+lineid+'] td.linecoluht a').html(data.price);
			  						
			  						$link.html(data[col]);
			  						
			  					});
			  					
			  					
				  				
			  				});
			  				
			  			});
			  			
			  		});
			  		
		  		});
		  		
		  	</script>
		  	
		  	<?php
		  
		}

		if (! $error)
		{
			$this->results = array('myreturn' => $myvalue);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		}
		else
		{
			$this->errors[] = 'Error message';
			return -1;
		}
	}
}