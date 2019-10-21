<?php

	require('../config.php');
	dol_include_once('/comm/propal/class/propal.class.php');
	dol_include_once('/compta/facture/class/facture.class.php');
	dol_include_once('/commande/class/commande.class.php');
	dol_include_once('/fourn/class/fournisseur.commande.class.php');
	dol_include_once('/fourn/class/fournisseur.facture.class.php');
	dol_include_once('/supplier_proposal/class/supplier_proposal.class.php');
	
	$put = GETPOST('put');
	$get = GETPOST('get');
	$objectid = GETPOST('objectid');
	$objectelement = GETPOST('objectelement');
	$lineid = GETPOST('lineid');
	$lineclass = GETPOST('lineclass');
	$type = GETPOST('type');
	$value = GETPOST('value');
	$code_extrafield = GETPOST('code_extrafield');

	switch ($put) {
		case 'price':
			
			$Tab = _updateObjectLine(GETPOST('objectid'),GETPOST('objectelement'),GETPOST('lineid'),GETPOST('column'), GETPOST('value'));
					
			echo json_encode($Tab);	
			break;
		case 'extrafield-value':

			echo _saveExtrafield($lineid, $lineclass, $type, $code_extrafield, $value);
			break;
		
	}
	switch ($get) {
		case 'extrafield-value':

			echo _showExtrafield($objectelement, $lineid, $code_extrafield);
			break;

	}
	
function _updateObjectLine($objectid, $objectelement,$lineid,$column, $value) {
	global $db,$conf, $langs, $user, $hookmanager;
	$error=0;
	if($column == 'remise_percent') ${$column} = price2num(floatval($value));
	else ${$column} = price2num($value);
	
	$Tab = array();
	if ($objectelement == "order_supplier") $objectelement = "CommandeFournisseur";
	if ($objectelement == "invoice_supplier") $objectelement = "FactureFournisseur";
	if ($objectelement == "supplier_proposal") $objectelement = "SupplierProposal";
	
	$o=new $objectelement($db);
	$o->fetch($objectid);

	if(!empty($conf->global->QCP_ALLOW_CHANGE_ON_VALIDATE)) {
		$o->brouillon=1;
		$o->statut = $objectelement::STATUS_DRAFT;
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

				$label = ((GETPOST('update_label') && GETPOST('product_label')) ? GETPOST('product_label') : '');

				if (((!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->produit->ignore_price_min_advance)) || empty($conf->global->MAIN_USE_ADVANCED_PERMS) ) && ($price_min && (price2num($price) * (1 - price2num(floatval(GETPOST('remise_percent'))) / 100) < price2num($price_min))))
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

				$label = ((GETPOST('update_label') && GETPOST('product_label')) ? GETPOST('product_label') : '');

				if (((!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->produit->ignore_price_min_advance)) || empty($conf->global->MAIN_USE_ADVANCED_PERMS) )&& ($price_min && (price2num($price) * (1 - price2num(floatval(GETPOST('remise_percent'))) / 100) < price2num($price_min))))
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
		else if ($objectelement == "propal")
		{ // Propal
			if (!empty($line->fk_product))
			{
				$product = new Product($db);
				$res = $product->fetch($line->fk_product);

				$type = $product->type;

				$price_min = $product->price_min;
				if (!empty($conf->global->PRODUIT_MULTIPRICES) && !empty($o->thirdparty->price_level))
					$price_min = $product->multiprices_min [$o->thirdparty->price_level];

				$label = ((GETPOST('update_label') && GETPOST('product_label')) ? GETPOST('product_label') : '');

				if (((!empty($conf->global->MAIN_USE_ADVANCED_PERMS) && empty($user->rights->produit->ignore_price_min_advance)) || empty($conf->global->MAIN_USE_ADVANCED_PERMS) )&& ($price_min && (price2num($price) * (1 - price2num(floatval(GETPOST('remise_percent'))) / 100) < price2num($price_min))))
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
		else if ($objectelement == "CommandeFournisseur")
        {
        	if(isset($price) && intval($price) === 0) $line->multicurrency_subprice = 0;
            $res = $o->updateline($lineid, $line->desc, $price, $qty, $remise_percent, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, 'HT', $line->info_bits, $line->product_type, 0, $line->date_start, $line->date_end, $line->array_options, $line->fk_unit, $line->multicurrency_subprice, $line->ref_supplier);
            $total_ht = $o->line->total_ht;
            $uttc = $o->line->subprice + ($o->line->subprice * $o->line->tva_tx) / 100;
        }
		elseif ($objectelement == "FactureFournisseur")
        {
            $res = $o->updateline($lineid, $line->desc, $price, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, $qty, $line->fk_product, 'HT', $line->info_bits, $line->product_type, $remise_percent, false, $line->date_start, $line->date_end, $line->array_options, $line->fk_unit, $line->multicurrency_subprice, $line->ref_supplier);
            $line = new SupplierInvoiceLine($db);
            $line->fetch($lineid);

            $total_ht = $line->total_ht;
            $uttc = $line->subprice + ($line->subprice * $line->tva_tx) / 100;
        }
		elseif ($objectelement == "SupplierProposal")
        {
            $res = $o->updateline($lineid, $price, $qty, $remise_percent, $line->tva_tx, $line->localtax1_tx, $line->localtax2_tx, $line->desc);
            $line = new SupplierProposalLine($db);
            $line->fetch($lineid);

            $total_ht = $line->total_ht;
            $uttc = $line->subprice + ($line->subprice * $line->tva_tx) / 100;
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
		
		
		if($res>=0) {
		
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

    // Allow hooks to add more data to the JSON
    $parameters = array(
        'json_payload' => $Tab,
        'lineid' => $lineid,
        'objectelement' => $objectelement
    );
    $reshook = $hookmanager->executeHooks('addJSONPayload', $parameters, $o);
    switch ($reshook) {
        case -1:
            // error
            setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
            break;
        case 0:
            // merge
            if (!empty($hookmanager->resArray)) { $Tab = array_merge($Tab, $hookmanager->resArray); }
            break;
        case 1:
            // replace
            if (!empty($hookmanager->resArray)) { $Tab = $hookmanager->resArray; }
            break;
    }
	
	return $Tab;
	
}

function _showExtrafield($objectelement, $lineid, $code_extrafield) {
	global $db;
	if ($objectelement == "order_supplier") $lineclass = "CommandeFournisseurLigne";
	if ($objectelement == "invoice_supplier") $lineclass = "SupplierInvoiceLine";
	if ($objectelement == "supplier_proposal") $lineclass = "SupplierProposalLine";
	if ($objectelement == "facture") $lineclass = "FactureLigne";
	if ($objectelement == "commande") $lineclass = "OrderLine";
	if ($objectelement == "propal") $lineclass = "PropaleLigne";

	$extrafields = new ExtraFields($db);
	$line = new $lineclass($db);
	$line->fetch($lineid);
	$line->fetch_optionals();
	$extrafields->fetch_name_optionals_label($line->element);
	return $extrafields->showInputField($code_extrafield, $line->array_options['options_'.$code_extrafield])
		.'&nbsp;&nbsp;<span class="quickSaveExtra" style="cursor:pointer;" type="'.$extrafields->attribute_type[$code_extrafield].'" extracode="'.$code_extrafield.'" lineid="'.$lineid.'" lineclass="'.$lineclass.'"><i class="fa fa-check" aria-hidden="true"></i></span>';

}

function _saveExtrafield($lineid, $lineclass, $type, $code_extrafield, $value) {
	global $db;
	$line = new $lineclass($db);
	$line->fetch($lineid);
	$line->fetch_optionals();
	$extrafields = new ExtraFields($db);
	$extrafields->fetch_name_optionals_label($line->element);

	if($extrafields->attribute_type[$code_extrafield] == 'datetime' && !empty($value)) $value = (int) $value;

	if(is_array($value)) $value = implode(',', $value);
	$line->array_options['options_' . $code_extrafield] = $value;
	$line->update();

	return $extrafields->showOutputField($code_extrafield, $line->array_options['options_' . $code_extrafield]);

}
