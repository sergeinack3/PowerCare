{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $mode === "preview"}}
    <table class="main form">
        <tr>
            <td>
                {{mb_include module=cabinet template=inc_list_ant}}
            </td>
        </tr>
    </table>
{{else}}
    {{mb_script module=cabinet script=dossier_medical ajax=1}}

    {{mb_default var=dossier_anesth_id value=""}}
    <script>
        Main.add(function () {
            if (!DossierMedical.patient_id) {
                DossierMedical.sejour_id = '{{$sejour_id}}';
                DossierMedical._is_anesth = '{{$_is_anesth}}';
                DossierMedical.patient_id = '{{$patient->_id}}';
                DossierMedical.dossier_anesth_id = '{{$dossier_anesth_id}}';
            }

            {{if isset($consult|smarty:nodefaults) && $consult->type == "entree"}}
            DossierMedical.show_gestion_tp = false;
            {{/if}}

            DossierMedical.reloadDossiersMedicaux();
        });
    </script>
    {{mb_include module=cabinet template=inc_vw_list_antecedents show_header_dossier_patient=false}}
{{/if}}
