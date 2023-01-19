{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite script=dossierMater ajax=1}}

{{assign var=patient           value=$grossesse->_ref_parturiente}}
{{assign var=dossier_perinatal value=$grossesse->_ref_dossier_perinat}}
{{assign var=pere value=$grossesse->_ref_pere}}
{{assign var=surv_echographies value=$grossesse->_ref_surv_echographies}}
{{assign var=praticien   value=$grossesse->_ref_last_consult->_ref_praticien}}
{{assign var=constantes_maman  value=$dossier_perinatal->_ref_ant_mater_constantes}}
{{assign var=dossier_medical   value=$patient->_ref_dossier_medical}}

<table class="print">
  <tr>
    <th class="title">
      <a href="#" {{if !$offline}}onclick="window.print();"{{/if}} style="font-size: 1.3em;">
          {{tr}}CGrossesse-action-Summary sheet in Maternity{{/tr}}
      </a>

        {{if !$offline}}
          <button type="button" class="not-printable notext print" onclick="window.print()"
                  style="float:right">{{tr}}Print{{/tr}}</button>
        {{/if}}
    </th>
  </tr>
</table>

<table class="print">
  <tr>
    <td colspan="2">
      <table class="me-w100" style="font-size: 100%;">
          {{mb_include module=maternite template=synthese_grossesse/inc_infos_praticien}}
          {{mb_include module=maternite template=synthese_grossesse/inc_infos_parturiente}}
          {{if $pere->_id}}
              {{mb_include module=maternite template=synthese_grossesse/inc_infos_pere}}
          {{/if}}
      </table>
    </td>
  </tr>
  <tr>
    <td colspan="2">
        {{mb_include module=maternite template=synthese_grossesse/inc_current_pregnancy}}
    </td>
  </tr>
  <tr>
    <td class="halfPane">
        {{mb_include module=maternite template=synthese_grossesse/inc_atcds_parents}}
    </td>
    <td class="halfPane">
      <table class="me-w100" style="font-size: 100%;">
          {{mb_include module=maternite template=synthese_grossesse/inc_allergies_mere}}
          {{mb_include module=maternite template=synthese_grossesse/inc_produits_toxiques}}
      </table>
    </td>
  </tr>
  <tr>
    <td class="halfPane">
        {{mb_include module=maternite template=synthese_grossesse/inc_traitements_sejours_mere}}
    </td>
    <td class="halfPane">
        {{mb_include module=maternite template=synthese_grossesse/inc_sejours_grossesse}}
    </td>
  </tr>
</table>
<table class="print" style="page-break-before: always;border: 1px solid #90A4AE">
  <tr>
    <td colspan="2">
        {{mb_include module=maternite template=synthese_grossesse/inc_depistages}}
    </td>
  </tr>
  <tr>
    <td colspan="2">
        {{mb_include module=maternite template=synthese_grossesse/inc_resume_accouchement}}
    </td>
  </tr>
</table>
<table class="print me-no-box-shadow" style="page-break-before: always;">
  <tr>
    <td colspan="2">
        {{mb_include module=maternite template=synthese_grossesse/inc_surveillance_echographique}}
    </td>
  </tr>
</table>
