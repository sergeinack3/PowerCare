{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="list_doublons_dossier_medicaux">
  {{if $mode == "repair"}}
    <div class="small-info">
      {{tr}}CDossierMedical-to_correct{{/tr}}: {{$resultats|@count}}
      {{tr}}CDossierMedical-corrected{{/tr}}: {{$correction}}
    </div>
  {{/if}}
  <table class="tbl">
    <tr>
      <th class="title" colspan="3">{{tr}}CDossierMedical-to_correct{{/tr}}: {{$nb_results}}</th>
    </tr>
    <tr>
      <th>{{tr}}CPatient{{/tr}}</th>
      <th>{{tr}}CDossierMedical.all{{/tr}}</th>
      <th></th>
    </tr>
    {{mb_include module=system template=inc_pagination total=$nb_results current=$page change_page='changePageDoublonDossier'}}
    {{foreach from=$resultats item=_result}}
      <tr>
        <td>
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_result->_guid}}')">
            {{$_result->_view}}
          </span>
        </td>
        <td>
          <ul>
            {{foreach from=$_result->_ref_dossiers_medicaux item=_dossier_medical}}
              <li>
                <span onmouseover="ObjectTooltip.createEx(this, '{{$_dossier_medical->_guid}}')">
                  {{$_dossier_medical->_view}}
                </span>
              </li>
            {{/foreach}}
          </ul>
        </td>
        <td class="text">
          {{if $_result->_erreurs_correction|@count}}
            <ul>
              {{foreach from=$_result->_erreurs_correction item=_erreur}}
                <li>{{$_erreur|html_entity_decode}}</li>
              {{/foreach}}
            </ul>
          {{/if}}
        </td>
      </tr>
      {{foreachelse}}
      <tr>
        <td class="empty" colspan="3">{{tr}}CDossierMedical-none_to_corrected{{/tr}}</td>
      </tr>
    {{/foreach}}
    <tr>
      <td class="button" colspan="3">
        {{if $resultats|@count}}
          <button class="change" type="button" onclick="editAntecedent('repair');">
            {{tr}}CDossierMedical-action-correct{{/tr}}
          </button>
        {{else}}
          <button class="cancel" type="button" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</div>