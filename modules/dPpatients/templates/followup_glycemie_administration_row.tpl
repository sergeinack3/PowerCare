{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$administrations item=_administration}}
  <span class="texticon">{{$_administration->_ref_prise->_quantite_UI}} {{$_administration->_libelle_unite_prescription_short}}</span>
  ({{$_administration->dateTime|date_format:$conf.time}})
  <br>
{{/foreach}}
