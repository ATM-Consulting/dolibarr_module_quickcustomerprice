<?php

	require('../config.php');
	dol_include_once('/comm/propal/class/propal.class.php');
	dol_include_once('/compta/facture/class/facture.class.php');
	dol_include_once('/commande/class/commande.class.php');
	
	$put = GETPOST('put');
	
	switch ($put) {
		case 'price':
			
			$Tab = _updateLine(GETPOST('objectid'),GETPOST('objectelement'),GETPOST('lineid'),GETPOST('column'), GETPOST('value'));
					
			__out($Tab, 'json');	
			break;
		
	}
	
function _updateLine($objectid, $objectelement,$lineid,$column, $value) {
	global $db,$conf, $langs;
	
	${$column} = price2num($value);
	
	$Tab = array();
	
	$o=new $objectelement($db);
	$o->fetch($objectid);
	
	$find=false;
	foreach($o->lines as &$line) {
		if($line->id == $lineid || $line->rowid == $lineid) {
			$find=true;
			break;
		}
	}
	
	if($find) {
		if(is_null($qty))$qty = $line->qty;
		if(is_null($price))$price = $line->subprice;
		if(is_null($remise_percent))$remise_percent = $line->remise_percent;
		
		if($objectelement == 'facture') {
			$res = $o->updateline( $lineid, $line->desc , $price, $qty, $remise_percent, $line->date_start, $line->date_end, $line->tva_tx, $line->localtax1_tx,$line->localtax2_tx
					, 'HT', $line->info_bits, $line->product_type, $line->fk_parent_line, 0, $line->fk_fournprice, $line->pa_ht, $line->label, $line->special_code
					, $line->array_options,$line->situation_percent, $line->fk_unit);
			$total_ht = $o->line->total_ht;
		}
		else if($objectelement == 'commande') {
			$res = $o->updateline( $lineid, $line->desc , $price, $qty, $remise_percent, $line->tva_tx, $line->localtax1_tx,$line->localtax2_tx, 'HT', $line->info_bits
					, $line->date_start, $line->date_end, $line->product_type, $line->fk_parent_line, 0, $line->fk_fournprice, $line->pa_ht, $line->label, $line->special_code
					, $line->array_options, $line->fk_unit);
			$total_ht = $o->line->total_ht;
		}
		else {
			$res = $o->updateline( $lineid , $price, $qty, $remise_percent, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, $line->desc, 'HT', $line->info_bits, $line->special_code
					, $line->fk_parent_line, 0, $line->fk_fournprice , $line->pa_ht, $line->label, $line->product_type, $line->date_start, $line->date_end, $line->array_options, $line->fk_unit  );
			$total_ht = $o->line->total_ht;
			
		}
		
		if($res>0) {
		
			$Tab=array(
				'total_ht'=>price($total_ht)
		        ,'qty'=>$qty
		        ,'price'=>price($price)
		        ,'remise_percent'=>$remise_percent
			);
			
			
		}
		else{
			$Tab=array(
				'error'=>'updateFailed'
				,'msg'=>$o->error
			);
		}
	}
	else{
		$Tab=array(
			'error'=>'noline'
		);
	}
	
	
	
	return $Tab;
	
}
