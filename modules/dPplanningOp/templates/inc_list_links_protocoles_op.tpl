{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$protocole->_refs_links_protocoles_op item=_link_protocole_op}}
  <button type="button" class="remove" data-protocole_operatoire_dhe_id="{{$_link_protocole_op->_id}}"
          onclick="ProtocoleOp.removeProtocoleOp(this);" title="{{tr}}Delete{{/tr}}">
    {{$_link_protocole_op->_ref_protocole_operatoire->libelle}}
  </button>
{{/foreach}}