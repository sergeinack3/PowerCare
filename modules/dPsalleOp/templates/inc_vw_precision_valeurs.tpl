{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $valeurs|@count > 0}}
  <select class="me-margin-0" name="precision_valeur_id" onchange="loadConstantesMedicales($V($('select_context')));">
    <option value="">
      {{tr}}CPrecisionValeur.none{{/tr}}
    </option>
    {{foreach from=$valeurs item=_valeur}}
      <option value="{{$_valeur->_id}}" {{if $_valeur->_id == $evenement->precision_valeur_id}} selected="selected"{{/if}}>
        {{$_valeur->_view}}
      </option>
    {{/foreach}}
  </select>
{{else}}
  <span class="empty">{{tr}}CPrecisionValeur.none{{/tr}}</span>
{{/if}}
