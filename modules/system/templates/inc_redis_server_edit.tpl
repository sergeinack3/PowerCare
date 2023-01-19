{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form method="post" name="edit-redis-server-{{$server->_guid}}" onsubmit="return onSubmitFormAjax(this, {onComplete: Control.Modal.close})">
  <input type="hidden" name="del" value="0"/>
  {{mb_key object=$server}}
  {{mb_class object=$server}}

  <table class="form">
    <col style="width:50%;" />

    {{mb_include module=system template=inc_form_table_header object=$server show_notes=false}}

    <tr>
      <th>{{mb_label object=$server field=host}}</th>
      <td>{{mb_field object=$server field=host}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$server field=port}}</th>
      <td>{{mb_field object=$server field=port}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$server field=active}}</th>
      <td>{{mb_field object=$server field=active}}</td>
    </tr>

    {{if $server->_id}}
      <tr>
        <th>{{mb_label object=$server field=is_master}}</th>
        <td>{{mb_value object=$server field=is_master}}</td>
      </tr>
      <tr>
        <th>{{mb_label object=$server field=latest_change}}</th>
        <td>{{mb_value object=$server field=latest_change}}</td>
      </tr>
    {{/if}}

    <tr>
      <td class="button" colspan="2">
        {{if $server->_id}}
          <button class="modify">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="confirmDeletion(this.form, {typeName:'le serveur Redis',objName:'{{$server->_view|smarty:nodefaults|JSAttribute}}', ajax:1}, {onComplete: Control.Modal.close});">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button class="new">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>