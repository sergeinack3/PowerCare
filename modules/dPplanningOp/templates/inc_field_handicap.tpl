{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=onchange value=""}}

{{* sejour->handicap is deprecated but we must be backward compatible *}}
{{if $sejour->handicap}}
    <th>{{mb_label object=$sejour field=handicap}}</th>
    <td>{{mb_field object=$sejour field=handicap onchange=$onchange}}</td>
{{else}}
    <th>{{mb_label object=$sejour field=_handicap}}</th>
    <td colspan="3">
        <table>
            <tr>
                {{foreach from=$patient_handicap_list item=_handicap name=handicaps}}
                {{if $smarty.foreach.handicaps.iteration > 0 && $smarty.foreach.handicaps.iteration % 2}}
            </tr>
            <tr>
                {{/if}}

                {{assign var=has_handicap value=false}}
                {{if "Ox\Mediboard\Patients\CPatientHandicap::hasHandicap"|static_call:$patient->_refs_patient_handicaps:$_handicap}}
                    {{assign var=has_handicap value=true}}
                    <script>
                        Main.add(() => {
                                PatientHandicap.addHandicapToList('{{$_handicap}}');
                            }
                        );
                    </script>
                {{/if}}

                <td>
                  <label>
                    <input type="checkbox"
                           class="editSejour_handicap"
                           id="editSejour_handicap-{{$_handicap}}"
                           name="handicap-{{$_handicap}}"
                           value="{{$_handicap}}"
                           {{if $has_handicap}}checked{{/if}}
                           onchange="
                            PatientHandicap.updateHandicapList(this);
                            $V(this.form._handicap, PatientHandicap.handicap_list)">
                        {{tr}}CPatientHandicap.handicap.{{$_handicap}}{{/tr}}
                    </label>
                </td>
                {{/foreach}}
            </tr>
        </table>

        {{mb_field object=$sejour field=_handicap onchange=$onchange|smarty:nodefaults hidden=true}}
    </td>
{{/if}}
