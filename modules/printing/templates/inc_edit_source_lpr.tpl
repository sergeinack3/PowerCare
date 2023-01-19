{{*
 * @package Mediboard\Printing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  after_edit_source_lpr = function(id) {
    editSource(id, "CSourceLPR");
    refreshList();
  };
</script>

<form name="edit_source" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$source_lpr}}
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$source_lpr}}
  <input type="hidden" name="callback" value="after_edit_source_lpr" />
  
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$source_lpr colspan=4}}
    <tr>
      <th>{{mb_label object=$source_lpr field=name}}</th>
      <td>{{mb_field object=$source_lpr field=name}}</td>
      <th>{{mb_label object=$source_lpr field=host}}</th>
      <td>{{mb_field object=$source_lpr field=host}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$source_lpr field=port}}</th>
      <td>{{mb_field object=$source_lpr field=port}}</td>
      <th>{{mb_label object=$source_lpr field=printer_name}}</th>
      <td>{{mb_field object=$source_lpr field=printer_name size=30}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$source_lpr field=user}}</th>
      <td>{{mb_field object=$source_lpr field=user}}</td>
      <td colspan="2"></td>
    </tr>
    {{if $source_lpr->_id}}
      <tr>
        <td style="padding-top: 20px;">
          <button type="button" class="print" onclick="testPrint('{{$source_lpr->_class}}','{{$source_lpr->_id}}')">
            {{tr}}CSourceLPR.test_print{{/tr}}
          </button>
        </td>
        <td colspan="3" id="result_print"></td>
      </tr>
    {{/if}}
    <tr>
      <td colspan="4" class="button">
        <button class="modify me-primary">{{tr}}Save{{/tr}}</button>
        {{if $source_lpr->_id}}
          <button class="cancel" onclick="confirmDeletion(this.form, {
            typeName: 'la source LPR',
            objName:'{{$source_lpr->name|smarty:nodefaults|JSAttribute}}',
            ajax: true})" type="button">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>