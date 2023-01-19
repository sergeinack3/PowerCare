{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$object->_can->read}}
  <div class="small-info">
    {{tr}}{{$object->_class}}{{/tr}} : {{tr}}access-forbidden{{/tr}}
  </div>
  {{mb_return}}
{{/if}}

<table class="form">
  <tr>
    <th class="title" colspan="4">
      Dossier médical du
      {{tr}}{{$object->object_class}}{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="title">Antécédent(s)</th>
    {{if is_array($object->_ref_traitements)}}
      <th class="title">Traitement(s)</th>
    {{/if}}
    <th class="title">Diagnostic(s)</th>
  </tr>
  
  <tr>
    <td class="text">
      {{foreach from=$object->_ref_antecedents_by_type key=curr_type item=list_antecedent}}
        {{if $list_antecedent|@count}}
          <strong>
            {{tr}}CAntecedent.type.{{$curr_type}}{{/tr}}
          </strong>
          <ul>
            {{foreach from=$list_antecedent item=curr_antecedent}}
              <li>
                {{mb_value object=$curr_antecedent field="date"}}
                {{mb_value object=$curr_antecedent field="rques"}}
              </li>
            {{/foreach}}
          </ul>
        {{/if}}
        {{foreachelse}}
        <i>Pas d'antécédents</i>
      {{/foreach}}
    </td>
    
    {{if is_array($object->_ref_traitements)}}
      <td class="text">
        {{if $object->_ref_traitements|@count}}
        <ul>{{/if}}
          {{foreach from=$object->_ref_traitements item=curr_traitement}}
            <li>
              {{if $curr_traitement->fin}}
                Depuis {{mb_value object=$curr_traitement field="debut"}}
                jusqu'à {{mb_value object=$curr_traitement field="fin"}} :
              {{elseif $curr_traitement->debut}}
                Depuis {{mb_value object=$curr_traitement field="debut"}} :
              {{/if}}
              {{mb_value object=$curr_traitement field="traitement"}}
            </li>
            {{foreachelse}}
            <i>Pas de traitements</i>
          {{/foreach}}
          {{if $object->_ref_traitements|@count}}</ul>{{/if}}
      </td>
    {{/if}}
    
    <td class="text">
      {{if $object->_ext_codes_cim|@count}}
      <ul>{{/if}}
        {{foreach from=$object->_ext_codes_cim item=curr_code}}
          <li>
            <strong>{{$curr_code->code}}:</strong> {{$curr_code->libelle}}
          </li>
          {{foreachelse}}
          <i>Pas de diagnostics</i>
        {{/foreach}}
        {{if $object->_ext_codes_cim|@count}}</ul>{{/if}}
    </td>
  </tr>
</table>
