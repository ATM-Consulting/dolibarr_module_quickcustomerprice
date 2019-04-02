<?php

	require('../config.php');
	dol_include_once('/comm/propal/class/propal.class.php');
	dol_include_once('/compta/facture/class/facture.class.php');
	dol_include_once('/commande/class/commande.class.php');
	
	$put = GETPOST('put');
	
	switch ($put) {
		case 'price':
			
			$Tab = _updateObjectLine(GETPOST('objectid'),GETPOST('objectelement'),GETPOST('lineid'),GETPOST('column'), GETPOST('value'));
					
			echo json_encode($Tab);	
			break;
		
	}
	
function _updateObjectLine($objectid, $objectelement,$lineid,$column, $value) {
	global $db,$conf, $langs, $hookmanager;
	$error=0;
	${$column} = price2num($value);
	
	$Tab = array();
	
	$o=new $objectelement($db);
	$o->fetch($objectid);
	
	if(!empty($conf->global->QCP_ALLOW_CHANGE_ON_VALIDATE)) {
		$o->brouillon=1;		
	}
	
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
		if(empty($remise_percent)) $remise_percent = 0;
		if(is_null($situation_cycle_ref))$situation_cycle_ref = empty($line->situation_percent) ? 0 : $line->situation_percent;

		if ($objectelement == 'facture')
		{
			if (!empty($line->fk_product))
			{
				$product = new Product($db);
				$res = $product->fetch($line->fk_product);

				$type = $product->type;

				$price_min = $product->price_min;
				if (!empty($conf->global->PRODUIT_MULTIPRICES) && !empty($o->thirdparty->price_level))
					$price_min = $product->multiprices_min [$o->thirdparty->price_level];

				if ($price_min && (price2num($price) * (1 - price2num($remise_percent) / 100) < price2num($price_min)))
				{
					$langs->load('products');
					$res = -1;
					$o->error = $langs->trans("CantBeLessThanMinPrice", price(price2num($price_min, 'MU'), 0, $langs, 0, 0, - 1, $conf->currency));
					$error ++;
				}
			}
			if (empty($error))
			{
                if($remise_percent === 'Offert') $remise_percent = 100;
                if(strpos($situation_cycle_ref, '%') !== false) $situation_cycle_ref = substr($situation_cycle_ref, 0, -1); // Do not keep the '%'

				$res = $o->updateline($lineid, $line->desc, $price, $qty, $remise_percent, $line->date_start, $line->date_end, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx
					, 'HT', $line->info_bits, $line->product_type, $line->fk_parent_line, 0, $line->fk_fournprice, $line->pa_ht, $line->label, $line->special_code
					, $line->array_options, $situation_cycle_ref, $line->fk_unit);
				$total_ht = $o->line->total_ht;
				$uttc = $o->line->subprice + ($o->line->subprice * $o->line->tva_tx) / 100;
			}
		}
		else if ($objectelement == 'commande')
		{
			if (!empty($line->fk_product))
			{
				$product = new Product($db);
				$res = $product->fetch($line->fk_product);

				$type = $product->type;

				$price_min = $product->price_min;
				if (!empty($conf->global->PRODUIT_MULTIPRICES) && !empty($o->thirdparty->price_level))
					$price_min = $product->multiprices_min [$o->thirdparty->price_level];

				if ($price_min && (price2num($price) * (1 - price2num($remise_percent) / 100) < price2num($price_min)))
				{
					$langs->load('products');
					$res = -1;
					$o->error = $langs->trans("CantBeLessThanMinPrice", price(price2num($price_min, 'MU'), 0, $langs, 0, 0, - 1, $conf->currency));
					$error ++;
				}
			}
			if (empty($error))
			{
				$res = $o->updateline($lineid, $line->desc, $price, $qty, $remise_percent, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, 'HT', $line->info_bits
					, $line->date_start, $line->date_end, $line->product_type, $line->fk_parent_line, 0, $line->fk_fournprice, $line->pa_ht, $line->label, $line->special_code
					, $line->array_options, $line->fk_unit);
				$total_ht = $o->line->total_ht;
				$uttc = $o->line->subprice + ($o->line->subprice * $o->line->tva_tx) / 100;
			}
		}
		else
		{ // Propal
			if (!empty($line->fk_product))
			{
				$product = new Product($db);
				$res = $product->fetch($line->fk_product);

				$type = $product->type;

				$price_min = $product->price_min;
				if (!empty($conf->global->PRODUIT_MULTIPRICES) && !empty($o->thirdparty->price_level))
					$price_min = $product->multiprices_min [$o->thirdparty->price_level];

				if ($price_min && (price2num($price) * (1 - price2num($remise_percent) / 100) < price2num($price_min)))
				{
					$langs->load('products');
					$res = -1;
					$o->error =$langs->trans("CantBeLessThanMinPrice", price(price2num($price_min, 'MU'), 0, $langs, 0, 0, - 1, $conf->currency));
					$error ++;
				}
			}
			if(empty($error)){
				$res = $o->updateline($lineid, $price, $qty, $remise_percent, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, $line->desc, 'HT', $line->info_bits, $line->special_code
					, $line->fk_parent_line, 0, $line->fk_fournprice, $line->pa_ht, $line->label, $line->product_type, $line->date_start, $line->date_end, $line->array_options, $line->fk_unit);
				$total_ht = $o->line->total_ht;
				$uttc = $o->line->subprice + ($o->line->subprice * $o->line->tva_tx) / 100;
			}
		}

		if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
		{
			$hidedetails = (GETPOST('hidedetails', 'int') ? GETPOST('hidedetails', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0));
			$hidedesc = (GETPOST('hidedesc', 'int') ? GETPOST('hidedesc', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0));
			$hideref = (GETPOST('hideref', 'int') ? GETPOST('hideref', 'int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0));

			// Define output language
			$outputlangs = $langs;
			$newlang = GETPOST('lang_id', 'alpha');
			if (! empty($conf->global->MAIN_MULTILANGS) && empty($newlang))
				$newlang = !empty($o->client) ? $o->client->default_lang : $o->thirdparty->default_lang;
			if (! empty($newlang)) {
				$outputlangs = new Translate("", $conf);
				$outputlangs->setDefaultLang($newlang);
			}
			
			$ret = $o->fetch($o->id); // Reload to get new records
            $hookname = '';
            if($o->element == 'commande') $hookname = 'ordercard';
            if($o->element == 'propal') $hookname = 'propalcard';
            if($o->element == 'facture') $hookname = 'invoicecard';
            $hookmanager->initHooks(array($hookname, 'globalcard'));
			$o->generateDocument($o->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
		}
		
		
		if($res>0) {
		
			$Tab=array(
				'total_ht'=>price($total_ht)
		        ,'qty'=>$qty
		        ,'price'=>price($price)
			,'situation_cycle_ref'=>$situation_cycle_ref
		        ,'remise_percent'=>$remise_percent
		        ,'uttc'=>$uttc
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
