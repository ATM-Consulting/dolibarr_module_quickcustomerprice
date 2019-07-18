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
        global $user;

		$error = 0;
//var_dump($parameters['currentcontext']);		
		if ($parameters['currentcontext'] == 'propalcard' || $parameters['currentcontext'] == 'ordercard' || $parameters['currentcontext'] == 'invoicecard')
		{
			global $langs, $conf;

			dol_include_once('/compta/facture/class/facture.class.php');
			dol_include_once('/comm/propal/class/propal.class.php');
			dol_include_once('/commande/class/commande.class.php');

			if($object->statut > 0 && empty($conf->global->QCP_ALLOW_CHANGE_ON_VALIDATE)) return 0;

			$TIDLinesToChange = $this->_getTIDLinesToChange($object);
		  	?>
		  	<script type="text/javascript">
		  		$(document).ready(function() {
		  			
		  			<?php
		  			
		  			if( (float)DOL_VERSION<3.9 ) {
		  				
						?>
						var nb_col= $('table#tablelines tr.liste_titre').first().find('td:contains(<?php echo $langs->transnoentities('PriceUHT') ?>)').prevAll('td').length ;
						if(nb_col>0) {
							$('table#tablelines tr[id]').each(function(i,item) {
								$(item).find('td').eq(nb_col).addClass('linecoluht');
							});
						}
						
						var nb_col= $('table#tablelines tr.liste_titre').first().find('td:contains(<?php echo $langs->transnoentities('Qty') ?>)').prevAll('td').length ;
						if(nb_col>0) {
							$('table#tablelines tr[id]').each(function(i,item) {
								$(item).find('td').eq(nb_col).addClass('linecolqty');
							});
						}
						
						var nb_col= $('table#tablelines tr.liste_titre').first().find('td:contains(<?php echo $langs->transnoentities('ReductionShort') ?>)').prevAll('td').length ;
						if(nb_col>0) {
							$('table#tablelines tr[id]').each(function(i,item) {
								$(item).find('td').eq(nb_col).addClass('linecoldiscount');
							});
						
							
							<?php
								$moreColForTotal = 1;
							
								if (! empty($conf->margin->enabled) && empty($user->societe_id)) $moreColForTotal++; 
								if (! empty($conf->global->DISPLAY_MARGIN_RATES) && $user->rights->margins->liretous) $moreColForTotal++;
								if (! empty($conf->global->DISPLAY_MARK_RATES) && $user->rights->margins->liretous) $moreColForTotal++;
							
							?>
							if(nb_col>0) {
								$('table#tablelines tr[id]').each(function(i,item) {
									$(item).find('td').eq(nb_col+<?php echo $moreColForTotal ?>).addClass('linecolht');
								});
							}
						}	
						<?php
						
						
		  			}	
		  		
		  			
		  			?>
					var TIDLinesToChange = <?php echo json_encode($TIDLinesToChange); ?>;
		  			<?php
                        $strToFind = array();

                        // Pour les facture de situations, on peut modifier le P.U. HT et les Qtes uniquement s'il n'y a qu'une situation dans le cycle
                        if(! empty($user->rights->quickcustomerprice->edit_unit_price) && $object->element != 'facture'
                            || ! empty($user->rights->quickcustomerprice->edit_unit_price) && $object->element == 'facture' && $object->type != Facture::TYPE_SITUATION
                            || ! empty($user->rights->quickcustomerprice->edit_unit_price) && $object->element == 'facture' && $object->type == Facture::TYPE_SITUATION && empty($object->tab_previous_situation_invoice) && empty($object->tab_next_situation_invoice)) {
                            $strToFind[] = 'td.linecoluht';
                        }

                        // Pour les facture de situations, on peut modifier le P.U. HT et les Qtes uniquement s'il n'y a qu'une situation dans le cycle
                        if(! empty($user->rights->quickcustomerprice->edit_quantity) && $object->element != 'facture'
                            || ! empty($user->rights->quickcustomerprice->edit_quantity) && $object->element == 'facture' && $object->type != Facture::TYPE_SITUATION
                            || ! empty($user->rights->quickcustomerprice->edit_quantity) && $object->element == 'facture' && $object->type == Facture::TYPE_SITUATION && empty($object->tab_previous_situation_invoice) && empty($object->tab_next_situation_invoice)) {
                            $strToFind[] = 'td.linecolqty';
                        }
                        if(! empty($user->rights->quickcustomerprice->edit_discount)) $strToFind[] = 'td.linecoldiscount';
                    ?>
			  		$('table#tablelines tr[id]').find('<?php echo implode(',', $strToFind); ?>'+',td.linecolcycleref').each(function(i,item) {
			  			value = $(item).html();
			  			if(value=='&nbsp;')value='';
			  			
			  			lineid = $(item).closest('tr').attr('id').substr(4);

						if(TIDLinesToChange.indexOf(lineid) == -1) return;
			  			
			  			if($(item).hasClass('linecoldiscount')) {
			  				col='remise_percent';
			  			}
			  			else if($(item).hasClass('linecolqty')) {
			  				col='qty';
			  			}
                                                else if($(item).hasClass('linecolcycleref')) {
                                                        col='situation_cycle_ref';
                                                }
			  			else {
			  				col = 'price';
			  			}
			  			
			  			$a = $('<a class="blue" style="text-decoration:underline;cursor:text;" />');
			  			$a.attr('href', "javascript:;");
			  			$a.attr('value', value);
			  			$a.attr('col', col);
			  			$a.attr('lineid', lineid);
			  			$a.attr('objectid', '<?php echo $object->id; ?>');
			  			$a.attr('objectelement', '<?php echo $object->element; ?>');
			  			
			  			//if(value == '' || value=='&nbsp;') $(item).html('...');
			  			
			  			$(item).wrapInner($a);
			  			
			  			$(item).attr('align','right');
			  			
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
			  						if(data.error == null){
										$('tr[id=row-'+lineid+'] td.linecolht').html(data.total_ht);
										$('tr[id=row-'+lineid+'] td.linecoldiscount a').html((data.remise_percent == 0 || data.remise_percent == '') ? '&nbsp;' : data.remise_percent+'%');
										$('tr[id=row-'+lineid+'] td.linecolqty a').html(data.qty);
										$('tr[id=row-'+lineid+'] td.linecoluht a').html(data.price);
										$('tr[id=row-'+lineid+'] td.linecolcycleref a').html(data.situation_cycle_ref+'%');
										<?php if( (float)DOL_VERSION>3.8 ) { ?>
										  $('tr[id=row-'+lineid+'] td.linecoluttc').html(data.uttc);
										<?php } ?>				  						
										$link.attr('value',data[col]);
									}else if (data.error == 'updateFailed'){
										$('tr[id=row-'+lineid+'] td.linecoluht a').html(data.msg);
									}
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
			return 0; // or return 1 to replace standard code
		}
		else
		{
			return -1;
		}
	}

	private function _getTIDLinesToChange($object) {
		$TRes = array();
		
		if(! empty($object->lines)) {
			foreach($object->lines AS $line) {
				if(($line->info_bits & 2) != 2) { // On empêche l'édition des lignes issues d'avoirs et de d'acomptes
					$TRes[] = $line->id;
				}
			}
		}
			
		if($object->element == 'propal' && !empty($object->line)){ // New propal line
			$TRes[] = strval($object->line->id);
		}

		return $TRes;
	}
}
