{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$protocoles_op item=_protocole_op}}
    {{assign var=owner_icon value="group"}}
    {{if $_protocole_op->chir_id}}
      {{assign var=owner_icon value="user"}}
    {{elseif $_protocole_op->function_id}}
      {{assign var=owner_icon value="user-function"}}
    {{/if}}

    <li data-protocole_op_id="{{$_protocole_op->_id}}" data-libelle="{{$_protocole_op->libelle}}">
      <img style="float: right; clear: both; margin: -1px;"
           src="images/icons/{{$owner_icon}}.png" />
      <span class="view">{{$_protocole_op->_view}}</span>
    </li>
  {{/foreach}}
</ul>