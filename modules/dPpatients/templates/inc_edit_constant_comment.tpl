{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

</script>

<form name="editComment" action="?" method="post"
      onsubmit="return onSubmitFormAjax(this, function() {
        Control.Modal.close();
        submitConstantesMedicales(getForm('edit-constantes-medicales'));
      });">
  {{mb_class object=$comment}}
  {{mb_key object=$comment}}
  {{mb_field object=$comment field=constant hidden=true}}
  {{mb_field object=$comment field=constant_id hidden=true}}

  <input type="hidden" name="del" value="0" />

  <table class="form">
    <tr>
      <th class="title text" colspan="2">
        {{tr}}CConstantComment-title-{{if $comment->_id}}modify{{else}}create{{/if}}{{/tr}}
        {{tr}}For{{/tr}}
        {{tr}}CConstantesMedicales-{{$comment->constant}}{{/tr}}
      </th>
    </tr>
    <tr>
      <td colspan="2" style="height: 20px;"></td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$comment field=comment}}
      </th>
      <td>
        {{mb_field object=$comment field=comment form="editComment" aidesaisie="objectClass: 'CConstantesMedicales', filterWithDependFields: false, validateOnBlur: 0, property: 'comment'"}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="save" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
        {{if $comment->_id}}
          <button type="button" class="trash" onclick="$V(this.form.del, 1); this.form.onsubmit();">{{tr}}Delete{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>