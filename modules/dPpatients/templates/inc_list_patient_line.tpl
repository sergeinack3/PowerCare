{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=with_tr value=1}}
{{mb_default var=show_merge value=1}}

{{if $with_tr}}
<tr class="patientFile
  {{if ($patient->_id == $_patient->_id && $mode === "search")}}selected{{/if}}
  {{if $mode === "board" && $_patient->_count_consult_prat}}highlight_patient{{/if}}
  {{if $_patient->deces != null}}hatching{{/if}}
  "
    id="patientFile-{{$_patient->_guid}}">
    {{/if}}

    <td style="text-align: center;">
        <input type="checkbox" name="objects_id[]" value="{{$_patient->_id}}" class="merge"
               onclick="checkOnlyTwoSelected(this)"/>
    </td>

    {{if $_patient->_vip}}
        <td class="text" colspan="5">
            <a href="#{{$_patient->_guid}}" onclick="reloadPatient('{{$_patient->_id}}', this);">
                Patient confidentiel
            </a>
        </td>
    {{else}}
        <td>
            <div style="float: right;">
                {{mb_include module=system template=inc_object_notes object=$_patient}}
            </div>

            {{if $_patient->_id == $patVitale->_id}}
                <div style="float: right;">
                    <img src="images/icons/carte_vitale.png" alt="lecture vitale"
                         title="Bénéficiaire associé à la carte Vitale"/>
                </div>
            {{/if}}

            <div class="text noted">
                {{if $mode === "search"}}
                    <a href="#{{$_patient->_guid}}" onclick="reloadPatient('{{$_patient->_id}}', this);"
                       style="display: inline;">
                        {{mb_value object=$_patient field="_view"}}
                    </a>
                {{else}}
                    <span onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_guid}}')">
            {{mb_value object=$_patient field="_view"}}
            ({{mb_value object=$_patient field="_age"}})
          </span>
                    <span class="compact" style="display: block;">
            {{$_patient->adresse|spancate:35}}
          </span>
                    <span class="compact" style="display: block;">
            {{$_patient->cp}} {{$_patient->ville|spancate:35}}
          </span>
                {{/if}}

                {{mb_include module=patients template=inc_icon_bmr_bhre patient=$_patient}}
            </div>

        </td>
        <td>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$_patient->_guid}}')">
        {{mb_value object=$_patient field="naissance"}}
      </span>
        </td>
        {{if $mode === "search"}}
            <td class="text compact">
                <span style="white-space: nowrap;">{{$_patient->adresse|spancate:30}}</span>
                <span style="white-space: nowrap;">{{$_patient->cp}} {{$_patient->ville|spancate:20}}</span>
            </td>
        {{/if}}

        {{assign var=_patient_id value=$_patient->_id}}
        <td
          title="{{if $sejours_avenir.$_patient_id}}{{tr}}CSejour-msg-avenir{{/tr}}{{/if}} {{if $sejours_encours.$_patient_id}}{{tr}}CSejour-msg-encours{{/tr}}{{/if}}">
            {{if $sejours_avenir.$_patient_id || $sejours_encours.$_patient_id}}
                <i class="far fa-hospital event-icon {{if $sejours_avenir.$_patient_id}}sejour-avenir{{/if}}
           {{if $sejours_encours.$_patient_id}}sejour-encours{{/if}} me-event-icon"></i>
            {{/if}}
        </td>
        <td>
            {{if $mode === "search"}}
                <a class="button search notext" href="?m=patients&tab=vw_full_patients&patient_id={{$_patient->_id}}"
                   title="Afficher le dossier complet" style="margin: -1px;">
                    {{tr}}Show{{/tr}}
                </a>
            {{else}}
                <button type="button" class="right notext me-tertiary me-dark"
                        onclick="
                          TdBTamm.loadTdbPatient('{{$_patient->_id}}');
                          TdBTamm.changeCurrentPat('patientFile-{{$_patient->_guid}}')
                          ">
                    {{tr}}Show{{/tr}}
                </button>
            {{/if}}
        </td>
    {{/if}}
    {{if $with_tr}}
</tr>
{{/if}}
