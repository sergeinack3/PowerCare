{{*
 * @package Mediboard\Printing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  after_edit_source_smb = function(id) {
    editSource(id, "CSourceSMB");
    refreshList();
  };
</script>

<form name="edit_source" method="post" onsubmit="return onSubmitFormAjax(this);">
  {{mb_class object=$source_smb}}
  <input type="hidden" name="del" value="0" />
  {{mb_key object=$source_smb}}
  <input type="hidden" name="callback" value="after_edit_source_smb" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$source_smb colspan=4}}

    <tr>
      <td>{{mb_label object=$source_smb field=name}}</td>
      <td>{{mb_field object=$source_smb field=name}}</td>
      <td>{{mb_label object=$source_smb field=host}}</td>
      <td>{{mb_field object=$source_smb field=host}}</td>
    </tr>
    <tr>
      <td>{{mb_label object=$source_smb field=port}}</td>
      <td>{{mb_field object=$source_smb field=port}}</td>
      <td>{{mb_label object=$source_smb field=printer_name}}</td>
      <td>{{mb_field object=$source_smb field=printer_name size=30}}</td>
    </tr>
    <tr>
      <td>{{mb_label object=$source_smb field=user}}</td>
      <td>{{mb_field object=$source_smb field=user}}</td>
      <td>{{mb_label object=$source_smb field=password}}</td>
      <td>{{mb_field object=$source_smb field=password}}</td>
    </tr>
    <tr>
      <td>{{mb_label object=$source_smb field=workgroup}}</td>
      <td colspan="3">{{mb_field object=$source_smb field=workgroup}}</td>
    </tr>
    {{if $source_smb->_id}}
      <tr>
        <td style="padding-top: 20px;">
          <button type="button" class="print" onclick="testPrint('{{$source_smb->_class}}','{{$source_smb->_id}}')">
            {{tr}}CSourceLPR.test_print{{/tr}}
          </button>
        </td>
        <td colspan="3" id="result_print"></td>
      </tr>
    {{/if}}
    <tr>
      <td colspan="4" class="button">
        <button class="modify me-primary">{{tr}}Save{{/tr}}</button>
        {{if $source_smb->_id}}
          <button class="cancel" onclick="confirmDeletion(this.form, {
            typeName: 'la source SMB',
            objName:'{{$source_smb->name|smarty:nodefaults|JSAttribute}}',
            ajax: true})" type="button">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>