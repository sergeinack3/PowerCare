{{*
 * @package Mediboard\Soins
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{unique_id var=unique_atcd}}
{{mb_default var=dossier_medical_sejour value=0}}
{{mb_default var=atcd_absence value=0}}

<!-- Affichage des autres antecedents -->
 {{if $dossier_medical->_count_antecedents > 0 || ($dossier_medical_sejour && $dossier_medical_sejour->_count_antecedents > 0)}}
   <span class="texticon texticon-atcd" onmouseover="ObjectTooltip.createDOM(this, 'antecedents{{$sejour_id}}_{{$unique_atcd}}')">{{tr}}CAntecedent-court{{/tr}}</span>

  <div id="antecedents{{$sejour_id}}_{{$unique_atcd}}" style="text-align:left;  display: none;">
    <table class="tbl me-no-box-shadow">
      <tr>
        <th {{if $dossier_medical->_count_antecedents && $dossier_medical_sejour && $dossier_medical_sejour->_count_antecedents}}colspan="2"{{/if}} class="title">
          {{tr}}CAntecedent.more{{/tr}} {{if $dossier_medical_sejour && $dossier_medical_sejour->_count_antecedents && !$dossier_medical->_count_antecedents}}significatifs{{/if}}
        </th>
      </tr>
      {{if $dossier_medical_sejour && $dossier_medical_sejour->_count_antecedents && $dossier_medical->_count_antecedents}}
        <th class="category">
          Significatifs
        </th>
        <th class="category">
          Autres antécédents
        </th>
      {{/if}}
      <tr>
        {{if $dossier_medical_sejour && $dossier_medical_sejour->_count_antecedents}}
          <td class="halfPane" style="padding: 0; vertical-align: top;">
            {{mb_include module=soins template=inc_list_antecedents antecedents=$antecedents_sejour dossier_medical=$dossier_medical_sejour}}
          </td>
        {{/if}}
        {{if $dossier_medical->_count_antecedents}}
          <td class="halfPane" style="padding: 0; vertical-align: top;">
            {{mb_include module=soins template=inc_list_antecedents antecedents=$antecedents dossier_medical=$dossier_medical}}
          </td>
        {{/if}}
      </tr>
    </table>

    {{if isset($sejours|smarty:nodefaults) && $sejours|@count}}
      <table class="tbl me-no-box-shadow">
        <tr>
          <th>Motif des séjours précédents du patient</th>
        </tr>
        {{foreach from=$sejours item=_sejour}}
          {{if $_sejour->_motif_complet != "[Att] "}}
            <tr>
              <td>
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_sejour->_guid}}')">
                  {{$_sejour->entree|date_format:"%m/%Y"}}: {{$_sejour->_motif_complet|nl2br}}
                </span>
              </td>
            </tr>
          {{/if}}
        {{/foreach}}
      </table>
    {{/if}}
  </div>
 {{elseif ($dossier_medical->_all_antecedents|@count - $dossier_medical->_ref_allergies|@count) > 0 || ($dossier_medical_sejour && ($dossier_medical_sejour->_all_antecedents|@count - $dossier_medical_sejour->_ref_allergies|@count) > 0)}}
   <span class="texticon texticon-allergies-ok" title="{{tr}}CAntecedent-No known atcd-desc{{/tr}}">{{tr}}CAntecedent-court{{/tr}}</span>
 {{/if}}
