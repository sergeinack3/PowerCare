{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cim10      script=CIM             ajax=1}}
{{mb_script module=cabinet    script=dossier_medical ajax=1}}
{{mb_script module=patients   script=patient         ajax=true}}

{{mb_default var=dossier_anesth_id value=""}}
{{mb_default var=context_date_min value=null}}
{{mb_default var=context_date_max value=null}}

<script>
    var cim10url = new Url;

    reloadCim10 = function (sCode) {
        var oForm = getForm("addDiagFrm");

        oCimField.add(sCode);

        {{if $_is_anesth}}
        if (DossierMedical.sejour_id) {
            oCimAnesthField.add(sCode);
        }
        {{/if}}
        $V(oForm.code_diag, '');
        $V(oForm.keywords_code, '');
    };

    easyMode = function () {
        var url = new Url("cabinet", "vw_ant_easymode");
        url.addParam("patient_id", "{{$patient->_id}}");
        {{if isset($consult|smarty:nodefaults)}}
        url.addParam("consult_id", "{{$consult->_id}}");
        {{/if}}
        url.pop(900, 600, "Mode grille");
    };

    Main.add(function () {
        if (!DossierMedical.patient_id) {
            DossierMedical.sejour_id = '{{$sejour_id}}';
            DossierMedical._is_anesth = '{{$_is_anesth}}';
            DossierMedical.patient_id = '{{$patient->_id}}';
            DossierMedical.dossier_anesth_id = '{{$dossier_anesth_id}}';
            DossierMedical.context_date_max = '{{$context_date_max}}';
            DossierMedical.context_date_min = '{{$context_date_min}}';
        }

        {{if isset($consult|smarty:nodefaults) && $consult->type == "entree"}}
        DossierMedical.show_gestion_tp = false;
        {{/if}}

        DossierMedical.reloadDossiersMedicaux();

        if (Grossesse && Grossesse.view_light) {
          Grossesse.view_light = '0';
        }
    });
</script>

<table class="main">
    {{mb_default var=show_header value=0}}
    {{if $show_header}}
        <tr>
            <th class="title" colspan="2">
                <a style="float: left" href="?m=patients&tab=vw_full_patients&patient_id={{$patient->_id}}">
                    {{mb_include module=patients template=inc_vw_photo_identite size=42}}
                </a>

                <h2 style="color: #fff; font-weight: bold;">
                    {{$patient}}
                    {{if isset($sejour|smarty:nodefaults)}}
                        <span style="font-size: 0.7em;"> - {{$sejour->_shortview|replace:"Du":"Séjour du"}}</span>
                    {{/if}}
                </h2>
            </th>
        </tr>
    {{/if}}

    <tr>
        <td class="halfPane">
            <table class="form me-no-box-shadow me-no-align">
                <tr>
                    <td class="button">
                        <button class="edit me-tertiary me-dark" type="button" onclick="easyMode();"
                                {{if "dPpatients CAntecedent create_antecedent_only_prat"|gconf && !$app->user_prefs.allowed_to_edit_atcd &&
                                !$app->_ref_user->isPraticien() && !$app->_ref_user->isSageFemme()}}style="display:none;"{{/if}}>
                            Mode grille
                        </button>
                        {{if "appFineClient"|module_active && "appFineClient Sync allow_appfine_sync"|gconf}}
                            {{mb_include module=appFineClient template=inc_show_ant}}
                        {{/if}}

                        {{if $patient->_ref_family_patient && ($patient->_ref_family_patient->parent_id_1 || $patient->_ref_family_patient->parent_id_2)}}
                            {{assign var=context_class value=$patient->_class}}
                            {{assign var=context_id    value=$patient->_id}}
                            <button type="button"
                                    onclick="Patient.getAntecedentParents('{{$patient->_id}}', '{{$context_class}}', '{{$context_id}}');">
                                <i
                                  class="far fa-eye"></i> {{tr}}CAntecedent-action-See the antecedent of the parents{{/tr}}
                            </button>
                        {{/if}}
                    </td>
                </tr>
                {{mb_include module=cabinet template=inc_ant_consult_trait}}
            </table>
        </td>
        <td class="halfPane">
            {{mb_include module=cabinet template=inc_vw_list_antecedents}}
        </td>
    </tr>
</table>
