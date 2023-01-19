{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=one_timing_filled value=false}}

{{mb_script module=salleOp script=salleOp ajax=1}}

{{assign var=check_identity_pat value="dPsalleOp COperation check_identity_pat"|gconf}}

{{if $modal}}
  <div id="timing">
{{/if}}

{{if @$modules.brancardage->_can->read && "brancardage General use_brancardage"|gconf && $selOp->_ref_brancardage->_id}}
  <form name="changeItemBrancard" method="post" action="">
    <input type="hidden" name="brancardage_item_id" value="" />
    <input type="hidden" name="@class" value="CBrancardage" />
    <input type="hidden" name="brancardage_id" value="{{$selOp->_ref_brancardage->_id}}" />
    <input type="hidden" name="demande_brancard" value="now" />
  </form>
{{/if}}

{{if $app->_ref_user->isPraticien() && !$app->user_prefs.chir_modif_timing}}
  {{assign var=modif_operation value=false}}
{{/if}}

{{if $operation_header}}
  {{mb_include module=salleOp template=inc_header_operation patient=$selOp->_ref_sejour->_ref_patient sejour=$selOp->_ref_sejour}}
{{/if}}

<table class="form me-small-form">
  <tr>
    <th class="title" colspan="100">
      {{if $check_identity_pat && $one_timing_filled && $modif_operation && !$selOp->validation_timing}}
        <button type="button" class="edit notext" style="float: right"
                onclick="SalleOp.topHoraires('{{$selOp->_id}}')">
          {{tr}}COperation-Modify timings{{/tr}}
        </button>
      {{/if}}

      {{tr}}COperation-msg-horodatage{{/tr}}
    </th>
  </tr>
  <tr>
    <td class="me-padding-0 {{if $check_identity_pat && !$one_timing_filled}}button{{/if}}">
      {{mb_include module=dPsalleOp template=inc_vw_timings}}
    </td>
    {{if 'dPsalleOp COperation garrots_multiples'|gconf && "dPsalleOp timings use_garrot"|gconf}}
      <td id="vw_garrots_{{$selOp->_id}}">
        {{mb_include module=dPsalleOp template=vw_garrots operation=$selOp}}
      </td>
    {{/if}}
</table>

{{if $modal}}
  </div>
{{/if}}
