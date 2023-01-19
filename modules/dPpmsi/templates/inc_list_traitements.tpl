{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <td class="text" colspan="2">
    <ul>
      <li>Du patient
        <ul>
          {{foreach from=$patient->_ref_dossier_medical->_ref_traitements item=curr_trmt}}
          <li>
            {{if $curr_trmt->fin}}
              Depuis {{mb_value object=$curr_trmt field=debut}}
              jusqu'à {{mb_value object=$curr_trmt field=fin}} :
            {{elseif $curr_trmt->debut}}
              Depuis {{mb_value object=$curr_trmt field=debut}} :
            {{/if}}
            <em>{{$curr_trmt->traitement}}</em>
          </li>
          {{foreachelse}}
            {{if $patient->_ref_dossier_medical->absence_traitement}}
              <li class="empty">{{tr}}CTraitement.absence{{/tr}}</li>
            {{else}}
              <li class="empty">{{tr}}CTraitement.none{{/tr}}</li>
            {{/if}}
          {{/foreach}}
        </ul>
      </li>
      <li>Significatifs du séjour
        <ul>
          {{foreach from=$sejour->_ref_dossier_medical->_ref_traitements item=curr_trmt}}
          <li>
            {{if $curr_trmt->fin}}
              Depuis {{mb_value object=$curr_trmt field=debut}} 
              jusqu'à {{mb_value object=$curr_trmt field=fin}} :
            {{elseif $curr_trmt->debut}}
              Depuis {{mb_value object=$curr_trmt field=debut}} :
            {{/if}}
            <em>{{$curr_trmt->traitement}}</em>
          </li>
          {{foreachelse}}
            <li class="empty">{{tr}}CTraitement.none{{/tr}}</li>
          {{/foreach}}
        </ul>
      </li>
    </ul>
  </td>
</tr>