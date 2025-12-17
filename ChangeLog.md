# Change Log for Quick Customer Price

## Unreleased

## Release 3.8
- FIX : T6251 - Fix edit extrafields on proposal lines - *17/12/2025* - 3.8.11
- FIX: DA027130 - Fix margin rate on price update and remove location.reload() to prevent duplicate line additions. - *13/10/2025* - 3.8.10
- FIX : Compat V22 - *02/10/2025* - 3.8.9
- FIX : Calculate progress on invoice situation - *1/10/2025* - 3.8.8
- FIX : undefined arguments checkPriceMin() on updateObjectLine() propal/commande *30/09/2025* - 3.8.7
- FIX : DA026983 - Fix property statut deprecated in property status - *12/09/2025* - 3.8.6
  + Fixed error handling when entering a price lower than the minimum price
- FIX : DA027049 - Correct incompatibility with INVOICE_USE_SITUATION == 2 - *18/08/2025* - 3.8.5
- FIX : Warning Constant INC_FROM_DOLIBARR already defined - *18/08/2025* - 3.8.5
- FIX : DA026769 - Formatting data before updateLine to respect std behavior - *31/07/2025* 3.8.4
- FIX : Add supplier ref for supplier propasal - *04/04/2025* - 3.8.3
- FIX : Add into showinputfields object id to query sql - *24/02/2025* - 3.8.2
- FIX : Compat V21 - *05/12/2024* - 3.8.1
- FIX (130) : Retrocompatibilité V16 - **05/12/2024** - 3.8.1
- NEW (SP128) : T5087 - Allow on-the-fly editing of margin rates and mark rates on quote, order and invoice lines - **13/11/2024** - 3.8.0

## Release 3.7

- FIX : Prendre en compte options d'affichage des colonnes marge / marque -
  **09/01/2025** - 3.7.1
- FIX : Compat v20 - **22/07/2024** - 3.7.0
  Changed Dolibarr compatibility range to 16 min - 20 max
  Changed PHP compatibility range to 7.1 min

## Release 3.6

- FIX : warning - **29/02/2024** - 3.6.1
- FIX : compat v19 - **21/11/2023** - 3.6.0

## Release 3.5

- NEW : Add hook to delete pen for some extrafields - **19/09/2023** - 3.5.0
- NEW : Allow on-the-fly editing of net P.U (currency) on quote, order and invoice lines (customer and supplier sides) -
  **11/03/2023** - 3.4.0
- NEW : Add spanish translation - **07/03/2023** - 3.3.0

## Release 3.2

- FIX : TK2502-3612 Allowed to set negative amount on credit note invoice - *2024-03-05* - 3.2.9
- FIX : DA023886 - pen for edition doesn't work - **18/09/2023** - 3.2.8
- FIX : DA023507 - Handle multiple class on element - **16/06/2023** - 3.2.7
- FIX : Update interface.php for bad value selection on link extrafields - **03/05/2023** - 3.2.6
- FIX : Compat V17 : gestion des extrafields - **15/12/2022** - 3.2.5
- FIX : Extrafields updates : `interface.php` has sql errors (invisible to user) due to number parsing - **05/10/2022
  ** - 3.2.4
- FIX : interpolation de variable dans l'ajax supprimé et mise à jour impossible - **12/09/2022** - 3.2.3
- FIX : Icon - **09/08/2022** - 3.2.2
- FIX : Retrait de certaines conditions sur des variables indéfinies, warning dans la log - Compat php8 - **12/07/2022
  ** - 3.2.1
- NEW : Ajout de la class TechATM pour l'affichage de la page "A propos" **10/05/2022** 3.2.0

## Release 3.1

- FIX: When you modify a supplier invoice line value the description of the modified line is erased. - **30/07/2022** -
  3.1.7
- FIX: change family name - **02/06/2022** - 3.1.6
- FIX: Compatibility V16 : newToken - **02/06/2022** - 3.1.5
- FIX: save for wrong line - **2022-03-29** - 3.1.4
- FIX: v14 compatibility quick extrafield - **2022-02-07** - 3.1.3
- FIX: minor v15 compatibility issues - **2022-01-31** - 3.1.2
- FIX: minor v14 compatibility issues - **2021-10-12** - 3.1.1
- no changelog up to this point - **2021-10-12** - 3.1.0
