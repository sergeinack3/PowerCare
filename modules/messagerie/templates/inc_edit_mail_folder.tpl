{{*
 * @package Mediboard\Messagerie
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  selectParent = function(field) {
    if (['inbox', 'archived', 'favorites', 'sentbox', 'drafts'].indexOf($V(field)) == '-1') {
      var option = $$('select[name="_folder"] option[value="' + $V(field) + '"]')[0];
      $V(field.form.elements['parent_id'], $V(field));
      $V(field.form.elements['type'], option.readAttribute('data-type'));
    }
    else {
      $V(field.form.elements['parent_id'], '');
      $V(field.form.elements['type'], $V(field));
    }
  };
</script>

<form name="editFolder" action="?" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close.curry());">
  {{mb_class object=$folder}}
  {{mb_key object=$folder}}

  {{mb_field object=$folder field=account_id hidden=true}}
  {{mb_field object=$folder field=type hidden=true}}
  <input type="hidden" name="del" value="0">

  <table class="form">
    <tr>
      <th class="title" colspan="2">
        {{if $folder->_id}}
          {{tr}}CUserMailFolder-title-modify{{/tr}}
        {{else}}
          {{tr}}CUserMailFolder-title-create{{/tr}}
        {{/if}}
      </th>
    </tr>
    <tr>
      <th>
        {{mb_label object=$folder field=name}}
      </th>
      <td>
        {{mb_field object=$folder field=name}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$folder field=description}}
      </th>
      <td>
        {{mb_field object=$folder field=description}}
      </td>
    </tr>
    <tr>
      <th>
        {{mb_label object=$folder field=parent_id}}
      </th>
      <td>
        {{mb_field object=$folder field=parent_id hidden=true}}
        {{mb_include module=messagerie template=inc_select_folders type=$folder->type parent_id=$folder->parent_id subject=$folder}}
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        {{if $folder->_id}}
          <button type="button" class="save" onclick="this.form.onsubmit();">{{tr}}Save{{/tr}}</button>
          <button type="button" class="trash" onclick="$V(this.form.elements['del'], '1'); this.form.onsubmit();">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button type="button" class="new" onclick="this.form.onsubmit();">{{tr}}Create{{/tr}}</button>
          <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Cancel{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>