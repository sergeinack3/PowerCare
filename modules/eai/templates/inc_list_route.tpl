{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="main tbl">
  <tr>
    <th colspan="9" class="title">
      {{tr}}CEAIRoute.list{{/tr}}
    </th>
  </tr>
  <tr>
    <th class="section" colspan="3">{{tr}}CInteropSender{{/tr}}</th>
    <th class="section" colspan="3">{{tr}}CInteropReceiver{{/tr}}</th>
    <th class="section" colspan="2"></th>
  </tr>
  <tr>
    <th> </th>
    <th> {{mb_title class=CEAIRoute field=sender_class}} </th>
    <th> {{mb_title class=CEAIRoute field=sender_id}} </th>
    <th> </th>
    <th> {{mb_title class=CEAIRoute field=receiver_class}} </th>
    <th> {{mb_title class=CEAIRoute field=receiver_id}} </th>
    <th> {{mb_title class=CEAIRoute field=active}} </th>
    <th> {{mb_title class=CEAIRoute field=description}} </th>
  </tr>

  {{foreach from=$routes key=_sender_guid item=_routes}}
    {{assign var=sender value=$senders.$_sender_guid}}
    <tbody class="hoverable">
    {{foreach from=$_routes item=_route name="foreach_routes"}}
      {{assign var=receiver value=$_route->_ref_receiver}}

        <tr {{if !$_route->active}}class="opacity-30"{{/if}}>
          {{if $smarty.foreach.foreach_routes.first}}
          <td rowspan="{{$_routes|@count}}" class="narrow button">
            <button type="button" class="add notext"
                    onclick="Route.add('{{$sender->_guid}}', Route.refreshList)">
              {{tr}}CInteropSender-add-route{{/tr}}</button>
          </td>
          <td rowspan="{{$_routes|@count}}" class="text">
           {{tr}} {{$sender->_class}} {{/tr}}
          </td>
          {{/if}}

          {{if $smarty.foreach.foreach_routes.first}}
          <td rowspan="{{$_routes|@count}}">
            <span onmouseover="ObjectTooltip.createEx(this, '{{$sender->_guid}}');">
               {{$sender->_view}}
             </span>
          </td>
          {{/if}}

          <td class="narrow button">
            <button type="button" class="edit notext"
                    onclick="Route.edit('{{$_route->_id}}', Route.refreshList)">
              {{tr}}Edit{{/tr}}
            </button>
          </td>
          <td class="text">
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
    </tbody>
  {{foreachelse}}
    <tr>
      <td colspan="9" class="empty">
        {{tr}}CEAIRoute.none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>