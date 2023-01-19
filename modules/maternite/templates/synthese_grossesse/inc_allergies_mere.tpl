{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="category" colspan="4">{{tr}}CAntecedent-Allergie|pl{{/tr}}</th>
</tr>
<tr>
  <td class="text" style="font-weight: bold; font-size:130%;">
      {{if $dossier_medical->_ref_antecedents_by_type && $dossier_medical->_ref_antecedents_by_type.alle|@count}}
          {{foreach from=$dossier_medical->_ref_antecedents_by_type.alle item=_antecedent_allergie}}
            <ul>
              <li>
                  {{if $_antecedent_allergie->date}}
                      {{mb_value object=$_antecedent_allergie field=date}} :
                  {{/if}}
                  {{$_antecedent_allergie->rques}}
              </li>
            </ul>
          {{/foreach}}
      {{else}}
        <ul>
          <li style="font-size: 0.8em;">{{tr}}Allergie-No allergy provided{{/tr}}</li>
        </ul>
      {{/if}}
  </td>
</tr>
