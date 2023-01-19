{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<option value="">{{tr}}CPrescription.protocole.select{{/tr}}</option>

{{if $without_pack}}
  <optgroup label="Protocoles du praticien">
  {{if $protocoles_list_praticien|@count}}
    {{foreach from=$protocoles_list_praticien item=proto}}
      <option value="{{$proto->_id}}" {{if $selected_id==$proto->_id}}selected="selected"{{/if}}>{{$proto->libelle}}</option>
    {{/foreach}}
  {{else}}
    <option value="" disabled="disabled">{{tr}}CPrescription.protocole.none{{/tr}}</option>
  {{/if}}
  </optgroup>
  <optgroup label="Protocoles de la fonction">
  {{if $protocoles_list_function|@count}}
    {{foreach from=$protocoles_list_function item=proto}}
      <option value="{{$proto->_id}}" {{if $selected_id==$proto->_id}}selected="selected"{{/if}}>{{$proto->libelle}}</option>
    {{/foreach}}
  {{else}}
     <option value="" disabled="disabled">{{tr}}CPrescription.protocole.none{{/tr}}</option>
  {{/if}}
  </optgroup>
{{else}}
  <optgroup label="Protocoles du praticien">
  {{if $protocoles_list_praticien|@count || $packs_praticien|@count}}
    {{foreach from=$protocoles_list_praticien item=proto}}
      <option value="prot-{{$proto->_id}}" {{if $selected_id==$proto->_id}}selected="selected"{{/if}}>{{$proto->libelle}}</option>
    {{/foreach}}
    {{foreach from=$packs_praticien item=_pack_praticien}}
      <option value="pack-{{$_pack_praticien->_id}}" style="font-weight: bold">{{$_pack_praticien->libelle}}</option>
    {{/foreach}}
  {{else}}
    <option value="" disabled="disabled">{{tr}}CPrescription.protocole.none{{/tr}}</option>
  {{/if}}
  </optgroup>

  <optgroup label="Protocoles de la fonction">
  {{if $protocoles_list_function|@count || $packs_function|@count}}
    {{foreach from=$protocoles_list_function item=proto}}
      <option value="prot-{{$proto->_id}}" {{if $selected_id==$proto->_id}}selected="selected"{{/if}}>{{$proto->libelle}}</option>
    {{/foreach}}
    {{foreach from=$packs_function item=_pack_function}}
      <option value="pack-{{$_pack_function->_id}}" style="font-weight: bold">{{$_pack_function->libelle}}</option>
    {{/foreach}}
  {{else}}
     <option value="" disabled="disabled">{{tr}}CPrescription.protocole.none{{/tr}}</option>
  {{/if}}
  </optgroup>
{{/if}}