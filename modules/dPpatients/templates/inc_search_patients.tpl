{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
    Main.add(function () {
        {{if $mode === "search"}}
        {{if $patient->_id}}
        reloadPatient('{{$patient->_id}}', 0);
        {{elseif $patients|@count == 1}}
        {{assign var=_first_patient value=$patients|@first}}
        reloadPatient('{{$_first_patient->_id}}', 0);
        {{/if}}
        {{/if}}

        var button_create = $("vw_idx_patient_button_create");
        if (button_create) {
            {{if $nom != '' || $prenom != ''}}
            button_create.show();
            {{else}}
            button_create.hide();
            {{/if}}
        }
    });
</script>

{{if "dPpatients CPatient limit_char_search"|gconf && ($nom != $nom_search || $prenom != $prenom_search)}}
    <div class="small-info">
        La recherche est volontairement limitée aux {{"dPpatients CPatient limit_char_search"|gconf}} premiers
        caractères
        <ul>
            {{if $nom != $nom_search}}
                <li>pour le <strong>nom</strong> : '{{$nom_search}}'</li>
            {{/if}}
            {{if $prenom != $prenom_search}}
                <li>pour le <strong>prénom</strong> : '{{$prenom_search}}'</li>
            {{/if}}
        </ul>
    </div>
{{/if}}

{{assign var=merge_only_admin value="dPpatients identitovigilance merge_only_admin"|gconf}}

{{if 'dPpatients CPatient search_paging'|gconf}}
    {{if $prat_id}}
        <div class="small-warning">
            {{tr}}dPpatients-msg-Practitioner selection does not allow paginated search.{{/tr}}
        </div>
    {{else}}
        {{mb_include module=system template=inc_pagination change_page='changePagePatient' total=$total current=$start step=$step}}
    {{/if}}
{{/if}}

<form name="fusion" method="get" onsubmit="return false;" class="me-no-align">
    <table class="tbl me-align-auto me-list-patients" id="list_patients">
        <tr>
            <th class="narrow">
                {{assign var=buttons_list value=""}}
                {{if ((!$merge_only_admin || $can->admin)) && $can->edit}}
                    {{me_button label=Merge icon=merge old_class=notext onclick="Patient.doMerge(getForm('fusion'))"}}
                {{/if}}

                {{me_button label=Link icon=link old_class=notext onclick="Patient.doLink(getForm('fusion'))"}}

                {{me_dropdown_button button_icon=opt button_class="notext me-tertiary" button_label=Options}}
            </th>
            <th id="inc_list_patient_th_patient">{{tr}}CPatient{{/tr}}</th>

            <th class="narrow">{{mb_title class=CPatient field=naissance}}</th>

            {{if $mode === "selector"}}
                {{if $patVitale && $patVitale->_id}}
                    <th>
                        {{mb_label object=$patVitale field=matricule}}
                    </th>
                {{else}}
                    <th>
                        {{mb_label class=CPatient field=tel}}<br/>{{mb_label class=CPatient field=tel2}}
                    </th>
                {{/if}}
            {{/if}}

            {{if $mode === "search" || $mode === "selector"}}
                <th>{{tr}}CPatient-adresse{{/tr}}</th>
            {{/if}}
            <th class="narrow"></th>
            <th class="narrow"></th>
        </tr>

        {{assign var=tabPatient value="vw_idx_patients&patient_id="}}
        {{assign var=patient_template value="inc_list_patient_line"}}

        {{if $mode === "board"}}
            {{assign var=tabPatient value="vw_full_patients&patient_id="}}
        {{/if}}

        {{if $mode === "selector"}}
            {{assign var=patient_template value="inc_line_pat_selector"}}
        {{/if}}

        <!-- Recherche exacte -->
        <tr>
            <th colspan="100" class="section">
                {{tr}}dPpatients-CPatient-exact-results{{/tr}}
                {{if ($patients|@count >= 30)}}({{tr}}thirty-first-results{{/tr}}){{/if}}
            </th>
        </tr>
        {{foreach from=$patients item=_patient}}
            {{mb_include module=patients template=$patient_template}}
            {{foreachelse}}
            <tr>
                <td colspan="100" class="empty">{{tr}}dPpatients-CPatient-no-exact-results{{/tr}}</td>
            </tr>
        {{/foreach}}

        <!-- Recherche avec chaines limitées -->
        {{if $patientsLimited|@count}}
            <tr>
                <th colspan="100" class="section">
                    {{tr}}dPpatients-CPatient-limited-results{{/tr}}
                    {{if ($patientsLimited|@count >= 30)}}({{tr}}thirty-first-results{{/tr}}){{/if}}
                </th>
            </tr>
        {{/if}}
        {{foreach from=$patientsLimited item=_patient}}
            {{mb_include module=patients template=$patient_template}}
        {{/foreach}}

        <!-- Recherche phonétique -->
        {{if $patientsSoundex|@count}}
            <tr>
                <th colspan="100" class="section">
                    {{tr}}dPpatients-CPatient-close-results{{/tr}}
                    {{if ($patientsSoundex|@count >= 30)}}({{tr}}thirty-first-results{{/tr}}){{/if}}
                </th>
            </tr>
        {{/if}}
        {{foreach from=$patientsSoundex item=_patient}}
            {{mb_include module=patients template=$patient_template}}
        {{/foreach}}
    </table>
</form>
