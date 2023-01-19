{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=eai script=route ajax=true}}

<fieldset>
  <legend>{{tr}}CEAIRoute.list{{/tr}}</legend>

  <table class="tbl">
    <tr>
      <td colspan="4">
        <button type="button" class="add"
                onclick="Route.add('{{$sender->_guid}}', InteropActor.refreshRoutes.curry('{{$sender->_guid}}'))">
          {{tr}}CInteropSender-add-route{{/tr}}
        </button>
      </td>
    </tr>
    <tr>
      <th class="section"> {{mb_title class=CEAIRoute field=receiver_class}} </th>
      <th class="section"> {{mb_title class=CEAIRoute field=receiver_id}} </th>
      <th class="section"> {{mb_title class=CEAIRoute field=active}} </th>
      <th class="section"> {{mb_title class=CEAIRoute field=description}} </th>
    </tr>
    {{foreach from=$routes item=_route}}
      {{assign var=receiver value=$_route->_ref_receiver}}

      <tr {{if !$_route->active}}class="opacity-30"{{/if}}>
        <td>
          <button type="button" class="edit notext"
                  onclick="Route.edit('{{$_route->_id}}', InteropActor.refreshRoutes.curry('{{$sender->_guid}}'))">
            {{tr}}Edit{{/tr}}
          </button>

          {{tr}} {{$receiver->_class}} {{/tr}}
        </td>

        <td>
         <span onmouseover="ObjectTooltip.createEx(this, '{{$receiver->_guid}}');">
           {{$receiver->_view}}
         </span>
        </td>

        <td>
          <form name="editActiveRoute{{$_route->_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
            {{mb_key object=$_route}}
            {{mb_class object=$_route}}
            {{mb_field object=$_route field="active" onchange=this.form.onsubmit()}}
          </form>
        </td>
        <td class="text compact">
          {{mb_value object=$_route field="description"}}
        </td>
      </tr>
    {{/foreach}}
  </table>
</fieldset>

