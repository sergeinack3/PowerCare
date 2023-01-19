{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=salleOp script=top_horaire ajax=true}}

{{assign var=sejour  value=$operation->_ref_sejour}}
{{assign var=patient value=$sejour->_ref_patient}}

<script>
  Main.add(function() {
    TopHoraire.operation_id = '{{$operation->_id}}';
  });
</script>

{{if $app->user_prefs.touchscreen === "1"}}
  <style>
    input[type="text"].time {
      width: 60px;
    }
  </style>
{{/if}}

{{mb_include module=salleOp template=inc_header_operation selOp=$operation show_hslip_button=0}}

{{if !$edit_after_induction}}
  <div class="small-error" style="font-size: 1.5em;">
    {{tr var1=$prev_op->_ref_patient->_view}}COperation-Prev op exit mandatory{{/tr}}
  </div>
{{/if}}

<table class="main tops_horaire_table">
  <tr>
    <td style="width: 25%;">
      {{mb_include module=salleOp template=inc_top_horaire timing=entree_bloc}}
    </td>
    <td style="width: 25%;">
      {{mb_include module=salleOp template=inc_top_horaire timing=remise_chir edit=$edit_after_induction}}
    </td>
    <td style="width: 25%;">
      {{mb_include module=salleOp template=inc_top_horaire timing=debut_op edit=$edit_after_induction}}
    </td>
    <td style="width: 25%;">
      {{mb_include module=salleOp template=inc_top_horaire timing=sortie_salle edit=$edit_after_induction}}
    </td>
  </tr>

  <tr>
    <td>
      {{mb_include module=salleOp template=inc_top_horaire timing=pec_anesth}}
    </td>
    <td>
      {{mb_include module=salleOp template=inc_top_horaire timing=entree_salle edit=$edit_after_induction}}
    </td>
    <td>
      {{mb_include module=salleOp template=inc_top_horaire timing=fin_op edit=$edit_after_induction}}
    </td>
    <td>
      {{mb_include module=salleOp template=inc_top_horaire timing=sortie_sans_sspi edit=$edit_after_induction}}
    </td>
  </tr>

  <tr>
    <td>
      {{mb_include module=salleOp template=inc_top_horaire timing=induction_debut}}
    </td>
    <td>
      {{mb_include module=salleOp template=inc_top_horaire timing=preparation_op edit=$edit_after_induction}}
    </td>
    <td>
      {{mb_include module=salleOp template=inc_top_horaire timing=remise_anesth edit=$edit_after_induction}}
    </td>
    <td></td>
  </tr>

  <tr>
    <td>
      {{mb_include module=salleOp template=inc_top_horaire timing=fin_pec_anesth edit=$edit_after_induction}}
    </td>
    <td></td>
    <td>
      {{mb_include module=salleOp template=inc_top_horaire timing=patient_stable edit=$edit_after_induction}}
    </td>
    <td></td>
  </tr>
</table>