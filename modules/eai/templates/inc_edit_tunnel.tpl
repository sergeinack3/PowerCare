{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editTunnel" method="POST" onsubmit="return CTunnel.submit(this)">
  {{mb_class object=$tunnel}}
  {{mb_key object=$tunnel}}
  <table class="form">
    <tr>
      {{if $tunnel->_id}}
      <th class="title modify text" colspan="2">
        {{mb_include module=system template=inc_object_idsante400 object=$tunnel}}
        {{mb_include module=system template=inc_object_history object=$tunnel}}

        {{tr}}{{$tunnel->_class}}-title-modify{{/tr}} '{{$tunnel}}'
        {{else}}
      <th class="title me-th-new" colspan="2">
        {{tr}}{{$tunnel->_class}}-title-create{{/tr}}
      {{/if}}
    </tr>
    <tr>
      <th>{{mb_label object=$tunnel field="address"}}</th>
      <td>{{mb_field object=$tunnel field="address"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$tunnel field="ca_file"}}</th>
      <td>{{mb_field object=$tunnel field="ca_file"}}</td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        {{if $tunnel->_id}}
          <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="CTunnel.confirmDeletion(this.form,{typeName:$T('CHTTPTunnelObject'),
                    objName:'{{$tunnel->_view|smarty:nodefaults|JSAttribute}}'})">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>