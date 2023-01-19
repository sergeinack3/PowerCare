{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=show_header_dossier_patient value=true}}
{{mb_default var=context_date_min value=null}}
{{mb_default var=context_date_max value=null}}

{{mb_script module=cabinet    script=dossier_medical ajax=1}}
<script>
    Main.add(function () {
        if (!DossierMedical.patient_id) {
            DossierMedical.sejour_id = '{{$sejour_id}}';
            DossierMedical._is_anesth = '{{$_is_anesth}}';
            DossierMedical.patient_id = '{{$patient->_id}}';
            DossierMedical.dossier_anesth_id = '{{$dossier_anesth_id}}';
            DossierMedical.context_date_max = '{{$context_date_max}}';
            DossierMedical.context_date_min = '{{$context_date_min}}';
        }
        DossierMedical.reloadDossiersMedicaux();
    });
</script>

<table class="form me-no-align">
    {{if $show_header_dossier_patient}}
        <tr>
            <th class="category me-text-align-left">Dossier patient</th>
        </tr>
    {{/if}}
    <tr>
        <td class="text" id="listAnt{{$sejour_id}}"></td>
    </tr>
    {{if $_is_anesth || $sejour_id}}
        <tr>
            <th class="category me-text-align-left">
                Eléments significatifs pour le séjour
            </th>
        </tr>
        <tr>
            <td class="text" id="listAntCAnesth{{$sejour_id}}"></td>
        </tr>
    {{/if}}
</table>
