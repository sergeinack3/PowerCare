{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<td colspan="{{if isset($colspan|smarty:nodefaults)}}{{$colspan}}{{/if}}">
  <select name="{{$name}}" style="width: 15em;" {{if $object->_class == "CFactureCabinet" || $object->_class == "CFactureEtablissement"}}onchange="return onSubmitFormAjax(this.form);"{{/if}}>
    <option value="" {{if !$object->$name}}selected="selected" {{/if}}>&mdash; {{tr}}CFacture.choose_assurance{{/tr}}</option>
    {{foreach from=$patient->_ref_correspondants_patient item=_assurance}}
      <option value="{{$_assurance->_id}}" {{if $object->$name == $_assurance->_id}} selected="selected" {{/if}}>
        {{$_assurance->nom}}  
        {{if $_assurance->date_debut && $_assurance->date_fin}}
          {{tr}}date.From{{/tr}} {{$_assurance->date_debut|date_format:$conf.date}} {{tr}}date.to{{/tr}} {{$_assurance->date_fin|date_format:$conf.date}}
        {{/if}}
      </option>
    {{/foreach}}
  </select>
</td>