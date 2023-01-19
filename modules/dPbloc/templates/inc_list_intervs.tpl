{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  operationEditCallback = function() { window.url_edit_planning.refreshModal();};

  ObjectTooltip.modes.allergies = {  
    module: "patients",
    action: "ajax_vw_allergies",
    sClass: "tooltip"
  };

  editRank = function(table) {
    table.select("div.rank").invoke("hide");
    table.select("select.toggle_rank").invoke("setStyle", "display: inline-block");
  };

  toggleRank = function(op_id, rank) {
    var form = getForm("toggleRankOp");
    $V(form.operation_id, op_id);
    $V(form.rank, rank);
    onSubmitFormAjax(form, reloadRightList);
  };

  Main.add(function() {
    var options = {
      exactMinutes: false, 
      minInterval : {{$conf.dPplanningOp.COperation.min_intervalle}},
      minHours    : {{$conf.dPplanningOp.COperation.duree_deb}},
      maxHours    : {{$conf.dPplanningOp.COperation.duree_fin}}
    };
    {{foreach from=$intervs item=_op}}
    oForm = getForm("edit-interv-{{$list_type}}-{{$_op->_id}}");
    Calendar.regField(oForm.temp_operation, null, options);
    Calendar.regField(oForm.duree_preop);
    if(oForm.pause) {
      Calendar.regField(oForm.pause);
      Calendar.regField(oForm.duree_bio_nettoyage);
    }
    {{/foreach}}
  });
</script>

{{if "kereon"|module_active && $list_type == "right"}}
  {{mb_include module=kereon template=inc_show_box_plot best_time_intervention=$best_time_intervention}}
{{/if}}

<table class="tbl">

{{if $list_type == "left"}}
  <tr>
    <th class="title" colspan="4">
      {{if !$seconde_plage->_id}}
      <form name="editOrderVoulu" method="post">
        <input type="hidden" name="m" value="bloc" />
        <input type="hidden" name="dosql" value="do_order_voulu_op" />
        <input type="hidden" name="plageop_id" value="{{$plage->_id}}" />
        <input type="hidden" name="del" value="0" />
        <button type="button" class="tick oneclick me-tertiary me-float-none" style="float: right;" onclick="submitOrder(this.form);">
          {{tr}}CPlageOp-action-Use the desired order{{/tr}}
        </button>
      </form>
      {{/if}}
      {{tr}}CPlageOp-Patients to place{{/tr}}
    </th>
  </tr>
{{else}}
  <tr>
    <th class="title" colspan="4">
      {{if !$seconde_plage->_id}}
        <button type="button" class="edit compact me-tertiary" style="float: left;" onclick="editRank(this.up('table'));">{{tr}}CPlageOp-action-Edit ranks{{/tr}}</button>
      {{/if}}
      {{tr}}CPlageOp-Order of interventions{{/tr}}
    </th>
  </tr>
{{/if}}

{{foreach from=$intervs item=_op name=list_intervs}}
  {{if $_op->_ref_prev_op && $_op->_ref_prev_op->_id && $_op->_ref_prev_op->plageop_id != $plage->_id}}
    {{mb_include module=bloc template=inc_interv_multisalle op=$_op->_ref_prev_op}}
  {{/if}}

  {{mb_include module=bloc template=inc_line_interv count_ops=$intervs|@count}}

  {{if $smarty.foreach.list_intervs.last && $_op->_ref_next_op && $_op->_ref_next_op->_id && $_op->_ref_next_op->plageop_id != $plage->_id}}
    {{mb_include module=bloc template=inc_interv_multisalle op=$_op->_ref_next_op}}
  {{/if}}
{{foreachelse}}
  <tr>
    <td class="empty">{{tr}}COperation.none{{/tr}}</td>
  </tr>
{{/foreach}}
</table>