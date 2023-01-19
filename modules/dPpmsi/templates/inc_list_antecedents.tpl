{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form">
  <tr>
    <th class="section halfPane" style="text-align: center">Du patient</th>
    <th class="section halfPane" style="text-align: center">Significatifs du séjour</th>
  </tr>
  <tr>
    <td class="text">
      <ul>
        {{foreach from=$patient->_ref_dossier_medical->_ref_antecedents_by_type key=curr_type item=list_antecedent}}
          {{if $list_antecedent|@count}}
            <li>
              {{tr}}CAntecedent.type.{{$curr_type}}{{/tr}}
              {{foreach from=$list_antecedent item=curr_antecedent}}
                <ul>
                  <li>
                    {{if $curr_antecedent->date}}
                      {{mb_value object=$curr_antecedent field=date}} -
                    {{/if}}
                    <em>{{$curr_antecedent->rques}}</em>
                  </li>
                </ul>
              {{/foreach}}
            </li>
          {{/if}}
          {{foreachelse}}
          <li class="empty">{{tr}}CAntecedent.none{{/tr}}</li>
        {{/foreach}}
      </ul>
    </td>
    <td class="text">
      <ul>
        {{foreach from=$sejour->_ref_dossier_medical->_ref_antecedents_by_type key=curr_type item=list_antecedent}}
          {{if $list_antecedent|@count}}
            <li>
              {{tr}}CAntecedent.type.{{$curr_type}}{{/tr}}
              {{foreach from=$list_antecedent item=curr_antecedent}}
                <ul>
                  <li>
                    {{if $curr_antecedent->date}}
                      {{mb_value object=$curr_antecedent field=date}} -
                    {{/if}}
                    <em>{{$curr_antecedent->rques}}</em>
                  </li>
                </ul>
              {{/foreach}}
            </li>
          {{/if}}
          {{foreachelse}}
          <li class="empty">{{tr}}CAntecedent.none{{/tr}}</li>
        {{/foreach}}
      </ul>
    </td>
  </tr>
</table>