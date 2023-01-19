{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="form me-margin-top-4 me-margin-bottom-4"
       style="{{if !$consult_anesth}}table-layout: fixed;{{/if}}{{if $synthese_rpu}}display: none;{{/if}}">
    <tr>
        <th class="category me-padding-2">{{tr}}CPatient-Patient folder{{/tr}}</th>
        {{if $consult_anesth}}
            <th class="category me-padding-2">{{tr}}CConsultation-back-consult_anesth{{/tr}}</th>
        {{/if}}
        <th class="category me-padding-2">{{tr}}CPatient-back-correspondants{{/tr}}</th>
        <th class="category me-padding-2">{{tr}}History{{/tr}}</th>
    </tr>

    <tr>
        <td class="button me-valign-top me-ws-wrap">
            {{if $consult->grossesse_id && "maternite CGrossesse audipog"|gconf}}
                {{mb_include module=maternite template=inc_button_suivi_grossesse}}
            {{else}}
                {{mb_include module=cabinet template=inc_patient_infos show_btn_dossier_complet=false}}
                <br class="me-no-display"/>
                {{mb_include module=patients template=inc_patient_planification patient_id=$patient->_id praticien_id=$userSel->_id consult_id=$consult->_id}}
                {{if "doctolib"|module_active && "doctolib staple_authentification client_access_key_id"|gconf}}
                    <br>
                    {{if isset($patient->_ref_doctolib_idex|smarty:nodefaults) && $patient->_ref_doctolib_idex->id400}}
                        {{mb_include module=doctolib template=buttons/inc_book}}
                        {{mb_include module=doctolib template=buttons/inc_patient_historic}}
                    {{else}}
                        {{mb_include module=doctolib template=buttons/inc_create_patient}}
                    {{/if}}
                {{/if}}
            {{/if}}
        </td>
        {{if $consult_anesth}}
            <td class="text" id="consultAnesth"></td>
        {{/if}}
        <td class="text me-valign-top">
            {{mb_include module=cabinet template=inc_patient_medecins}}
        </td>
        <td class="text me-valign-top me-text-align-center">
            {{mb_include module=cabinet template=inc_patient_history}}
        </td>
    </tr>
</table>
