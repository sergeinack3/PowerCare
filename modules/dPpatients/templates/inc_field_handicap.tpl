{{*
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=onchange value=""}}


<th>{{tr}}CSejour-handicap{{/tr}}</th>
<td colspan="3">
    <table>
        <tr>
            {{foreach from=$patient_handicap_list item=_handicap name=handicaps}}
            {{if $smarty.foreach.handicaps.iteration > 0 && $smarty.foreach.handicaps.iteration % 2}}
        </tr>
        <tr>
            {{/if}}

            {{assign var=has_handicap value=false}}
            {{foreach from=$patient->_refs_patient_handicaps item=_patient_handicap}}
                {{if $_patient_handicap->handicap == $_handicap}}
                    {{assign var=has_handicap value=true}}
                    <script>
                        Main.add(() => {
                                PatientHandicap.addHandicapToList('{{$_handicap}}');
                            }
                        );
                    </script>
                {{/if}}
            {{/foreach}}


            <td>
                <label>
                    <input type="checkbox"
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
    {{mb_field object=$patient field=_handicap onchange=$onchange|smarty:nodefaults hidden=true}}
</td>
