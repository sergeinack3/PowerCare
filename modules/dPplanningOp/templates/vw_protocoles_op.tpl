{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=planningOp script=protocole_op ajax=$ajax}}
{{mb_default var=order_col value='_ref_chir'}}
{{mb_default var=order_way value='ASC'}}
{{mb_default var=search_all_protocole_op value=1}}

<script>
  Main.add(function () {
    ProtocoleOp.refreshList('{{$order_col}}', '{{$order_way}}');
    ProtocoleOp.makeAutocompletes(getForm('filterProtocolesOp'));
  });
</script>

<div class="me-margin-top-4">
  <button class="new me-primary" onclick="ProtocoleOp.edit();">
    {{tr}}CProtocoleOperatoire-title-create{{/tr}}
  </button>

  {{if $can->admin}}
    <button class="search" onclick="ProtocoleOp.replaceProduct();">
      {{tr}}CProtocoleOperatoire-Replace products{{/tr}}
    </button>
  {{/if}}
</div>

<form name="filterProtocolesOp" method="get">
  <input type="hidden" name="order_col" value="{{$order_col}}"/>
  <input type="hidden" name="order_way" value="{{$order_way}}"/>
  <table class="form">
    <tr>
      {{me_form_field mb_object=$protocole_op mb_field=chir_id nb_cells=2}}
      {{mb_field object=$protocole_op field=chir_id hidden=true
      onchange="
                       \$V(this.form.function_id, '', false);
                       \$V(this.form.function_id_view, '', false);
                       \$V(this.form.group_id, '', false);
                       \$V(this.form.group_id_view, '', false);
                       \$V(this.form._search_all_protocole_op, '', false);
                       \$V(this.form.search_all_protocole_op, '', false);"}}
        <input type="text" name="chir_id_view" value="{{$protocole_op->_ref_chir->_view}}"/>
      {{/me_form_field}}

      {{me_form_field mb_object=$protocole_op mb_field=function_id nb_cells=2}}
      {{mb_field object=$protocole_op field=function_id hidden=true
      onchange="
                           \$V(this.form.chir_id, '', false);
                           \$V(this.form.chir_id_view, '', false);
                           \$V(this.form.group_id, '', false);
                           \$V(this.form.group_id_view, '', false);
                           \$V(this.form._search_all_protocole_op, '', false);
                           \$V(this.form.search_all_protocole_op, '', false);"}}
        <input type="text" name="function_id_view" value="{{$protocole_op->_ref_function->_view}}"/>
      {{/me_form_field}}
      <td>
        <label for="_search_all_protocole_op" title="{{tr}}CProtocoleOperatoire-Show groups protocols{{/tr}}">
          <input type="checkbox" name="_search_all_protocole_op" {{if $search_all_protocole_op}}checked{{/if}}
                 onclick="ProtocoleOp.showProcotolesGroup(this.form);">
          {{tr}}CProtocoleOperatoire-Show groups protocols{{/tr}}
        </label>
        <input type="hidden" name="search_all_protocole_op" value="{{$search_all_protocole_op}}"/>
      </td>
    </tr>
    <tr>
      <td colspan="6" class="button">
        <button type="button" class="search"
                onclick="ProtocoleOp.refreshList('{{$order_col}}','{{$order_way}}');">{{tr}}Filter{{/tr}}</button>
        <button type="button" class="print" onclick="ProtocoleOp.print();">{{tr}}Print{{/tr}}</button>
        <button type="button" class="hslip" onclick="ProtocoleOp.importCSV();">{{tr}}Import-CSV{{/tr}}</button>
        <button type="button" class="hslip" onclick="ProtocoleOp.exportCSV();">{{tr}}Export-CSV{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="protocoles_op_area" class="me-padding-0"></div>