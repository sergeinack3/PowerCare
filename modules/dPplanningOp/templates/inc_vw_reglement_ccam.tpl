{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="reglement-{{$acte_ccam->_id}}" method="post" action="">
  <input type="hidden" name="dosql" value="do_acteccam_aed" />
  <input type="hidden" name="m" value="dPsalleOp" />
  <input type="hidden" name="acte_id" value="{{$acte_ccam->_id}}" />
  <input type="hidden" name="_check_coded" value="0" />
  <input type="hidden" name="regle" value="{{$acte_ccam->regle}}" />
  <input type="hidden" name="regle_dh" value="{{$acte_ccam->regle_dh}}" />
     
  {{foreach from=$acte_ccam->_modificateurs item="modificateur"}}
    <input type="hidden" name="modificateur_{{$modificateur}}" value="on" />
  {{/foreach}}
 </form>