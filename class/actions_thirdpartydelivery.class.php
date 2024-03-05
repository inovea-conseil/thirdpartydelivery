<?php
/* Copyright (C) 2023 SuperAdmin
 * Copyright (C) 2024   William Mead    <william.mead@manchenumerique.fr>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    thirdpartydelivery/class/actions_thirdpartydelivery.class.php
 * \ingroup thirdpartydelivery
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsThirdpartyDelivery
 */
class ActionsThirdpartyDelivery
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param	array			$parameters		Array of parameters
	 * @param	CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string			$action      	'add', 'update', 'view'
	 * @return	int         					if KO: <0
	 *                           				|| if OK but we want to process standard actions too: =0
	 *                            				|| if OK and we want to replace standard actions: >0
	 */
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;
		$this->resprints = '';
		return 0;
	}
    public  function printFieldListWhere($parameters)
    {
        global $conf, $langs;

        if ($parameters["currentcontext"] == "supplierorderlist" && empty(GETPOST('button_removefilter_x', 'alpha'))) {
            $ThirdpartyDelivery = GETPOST('search_ThirdpartyDelivery', 'alpha');

            if (!empty($ThirdpartyDelivery)) {
                $this->resprints = "AND cf.rowid in (SELECT ec.element_id 
                FROM llx_element_contact as ec 
                INNER JOIN " . MAIN_DB_PREFIX . "socpeople as sp on ec.fk_socpeople = sp.rowid 
                INNER JOIN " . MAIN_DB_PREFIX . "societe as s on sp.fk_soc = s.rowid 
                ," . MAIN_DB_PREFIX . "c_type_contact as ctc
                WHERE ec.fk_c_type_contact = ctc.rowid and s.nom like '%".$ThirdpartyDelivery."%' 
                AND ctc.element = 'order_supplier' AND  ctc.source = 'external' AND ctc.code = 'SHIPPING')";
            }

        }
        return 1;
    }
    public function printFieldListOption($parameters)
    {
        global $conf, $langs;
        if ($parameters["currentcontext"] == "supplierorderlist") {
            if ( empty( $parameters['arrayfields']['c_ThirdpartyDelivery']) || $parameters['arrayfields']['c_ThirdpartyDelivery']['checked']) {
                $ThirdpartyDelivery = GETPOST('search_ThirdpartyDelivery', 'alpha');
                $search_remove_btn = GETPOST('button_removefilter_x', 'alpha');
                if (!empty($search_remove_btn)) {
                    $ThirdpartyDelivery="";
                }
                $res = '<td class="liste_titre left">';
                $res .= '<input class="flat" type="text" size="8" name="search_ThirdpartyDelivery" value="'.dol_escape_htmltag($ThirdpartyDelivery).'">';
                $res .= '</td>';
                $this->resprints = $res;
                return 0;
            }
        }
        return 1;
    }
    public function printFieldListTitle($parameters)
    {
        global $conf, $langs;
        $langs->load('thirdpartydelivery@thirdpartydelivery');
        if ($parameters["currentcontext"] == "supplierorderlist") {
            if (empty( $parameters['arrayfields']['c_ThirdpartyDelivery']) || $parameters['arrayfields']['c_ThirdpartyDelivery']['checked']) {
                $this->resprints = getTitleFieldOfList($langs->trans("ThirdpartyDelivery"), $_SERVER["PHP_SELF"], '', $parameters["param"], '', '', $parameters["sortfield"], $parameters["sortorder"], 'right ');
            }
            return 0;
        }
        return 1;
    }
    public function printFieldListValue($parameters, $object, $action)
    {
        require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
        require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
        require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
        global $conf,$db;
        if ($parameters["currentcontext"] == "supplierorderlist") {
            if ( empty( $parameters['arrayfields']['c_ThirdpartyDelivery']) || $parameters['arrayfields']['c_ThirdpartyDelivery']['checked']) {
                $supplierorderlist = new CommandeFournisseur($db);
                //$parameters['totalarray']['nbfield']++;
                $supplierorderlist->fetch($parameters['obj']->rowid);
                if (array_key_exists(0, $supplierorderlist->getIdContact('external', 'SHIPPING'))) {
					$contact = new Contact($db);
					$contact->fetch($supplierorderlist->getIdContact('external', 'SHIPPING')[0]);
					$tier = new Societe($db);
					$tier->fetch($contact->socid);
					print '<td class="tdoverflowmax150">' . $tier->getNomUrl(1) . '</td>';
                } else {
					print '<td class="tdoverflowmax150"></td>';
                }
            }
        }
        return 1;
    }

    public function printFieldListFooter($parameters) {
        global $conf,$db;
        if ($parameters["currentcontext"] == "supplierorderlist") {
            if ( empty( $parameters['arrayfields']['c_ThirdpartyDelivery']) || $parameters['arrayfields']['c_ThirdpartyDelivery']['checked']) {
                ?>
                    <script type="text/javascript">
                        $(document).ready(function () {
                            $(".liste_total").append("<td></td>");
                        });
                    </script>
                <?php
            }
        }
        return 1;
    }

	/**
	 * Overloading the doActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             on error: <0 || on success: 0 || to replace standard code: 1
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('supplierorderlist'))) {	    // do something only for the context 'somecontext1' or 'somecontext2'
            $langs->load('thirdpartydelivery@thirdpartydelivery');
            $parameters['arrayfields']['c_ThirdpartyDelivery'] = array(  "label" => $langs->trans("ThirdpartyDelivery"),'enabled'=>1, 'position'=>51);
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             on error: <0 || on success: 0 || to replace standard code: 1
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			foreach ($parameters['toselect'] as $objectid) {
				// Do action on each object id
			}
		}

		if (!$error) {
			$this->results = array('myreturn' => 999);
			$this->resprints = 'A text to show';
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}


	/**
	 * Overloading the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             on error: <0 || on success: 0 || to replace standard code: 1
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("ThirdpartyDeliveryMassAction").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}



	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$object		   	Object output on PDF
	 * @param   string	$action     	'add', 'update', 'view'
	 * @return  int                     if KO: <0
	 *                                  || if OK but we want to process standard actions too: =0
	 *                                  || if OK and we want to replace standard actions: >0
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}

	/**
	 * Execute action
	 *
	 * @param	array	$parameters     Array of parameters
	 * @param   Object	$pdfhandler     PDF builder handler
	 * @param   string	$action         'add', 'update', 'view'
	 * @return  int                    if KO: <0
	 *                                 || if OK but we want to process standard actions too: =0
	 *                                 || if OK and we want to replace standard actions: >0
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0; $deltemp = array();
		dol_syslog(get_class($this).'::executeHooks action='.$action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}



	/**
	 * Overloading the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             on error: <0 || on success: 0 || to replace standard code: 1
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$langs->load("thirdpartydelivery@thirdpartydelivery");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'thirdpartydelivery') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("ThirdpartyDelivery");
			$this->results['picto'] = 'thirdpartydelivery@thirdpartydelivery';
		}

		$head[$h][0] = 'customreports.php?objecttype='.$parameters['objecttype'].(empty($parameters['tabfamily']) ? '' : '&tabfamily='.$parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		return 1;
	}



	/**
	 * Overloading the restrictedArea function : check permission on an object
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             if KO: <0
	 *                                          || if OK but we want to process standard actions too: =0
	 *                                          || if OK and we want to replace standard actions: >0
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->rights->thirdpartydelivery->myobject->read) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}

	/**
	 * Execute action completeTabsHead
	 *
	 * @param   array           $parameters     Array of parameters
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         'add', 'update', 'view'
	 * @param   Hookmanager     $hookmanager    hookmanager
	 * @return  int                             if KO: <0
	 *                                          || if OK but we want to process standard actions too: =0
	 *                                          || if OK and we want to replace standard actions: >0
	 */
	public function completeTabsHead(&$parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user;

		if (!isset($parameters['object']->element)) {
			return 0;
		}
		if ($parameters['mode'] == 'remove') {
			// utilisé si on veut faire disparaitre des onglets.
			return 0;
		} elseif ($parameters['mode'] == 'add') {
			$langs->load('thirdpartydelivery@thirdpartydelivery');
			// utilisé si on veut ajouter des onglets.
			$counter = count($parameters['head']);
			$element = $parameters['object']->element;
			$id = $parameters['object']->id;
			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
			// if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
			if (in_array($element, ['context1', 'context2'])) {
				$datacount = 0;

				$parameters['head'][$counter][0] = dol_buildpath('/thirdpartydelivery/thirdpartydelivery_tab.php', 1) . '?id=' . $id . '&amp;module='.$element;
				$parameters['head'][$counter][1] = $langs->trans('ThirdpartyDeliveryTab');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'thirdpartydeliveryemails';
				$counter++;
			}
			if ($counter > 0 && (int) DOL_VERSION < 14) {
				$this->results = $parameters['head'];
				// return 1 to replace standard code
				return 1;
			} else {
				// en V14 et + $parameters['head'] est modifiable par référence
				return 0;
			}
		}
		return 0;
	}

	/* Add here any other hooked methods... */
}
