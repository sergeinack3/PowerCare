{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  selectParent = function(field) {
    $V(field.form.elements['folder_id'], $V(field));
  };
</script>

<form name="selectFolder" method="post" action="?" onsubmit="return false;">
  <table class="form">
    <tr>
      <th class="title" colspan="2">
        {{tr}}CUserMail-title-move{{/tr}}
      </th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$mail field=folder_id}}
      </th>
      <td>
        {{mb_field object=$mail field=folder_id hidden=true}}
        {{mb_include module=messagerie template=inc_select_folders type='' parent_id=$mail->folder_id select_types=false iteration_max=3}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="save" onclick="UserEmail.moveMail($V(this.form.elements['folder_id']){{if $mail->_id}}, '{{$mail->_id}}'{{/if}})">{{tr}}Select{{/tr}}</button>
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>