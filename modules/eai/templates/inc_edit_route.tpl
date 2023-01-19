{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
   Main.add(
    function() {
      Route.autocomplete_receiver();
      Route.autocomplete_sender();
    }
   )
</script>

<form name="editRoute" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close)">
  {{mb_key object=$route}}
  {{mb_class object=$route}}
  <table class="form">
    <tr>
      {{if $route->_id}}
        <th class="title modify text" colspan="2">
          {{mb_include module=system template=inc_object_idsante400 object=$route}}
          {{mb_include module=system template=inc_object_history object=$route}}
          {{tr}}{{$route->_class}}-title-modify{{/tr}} '{{$route}}'
      {{else}}
        <th class="title me-th-new" colspan="2">
          {{tr}}{{$route->_class}}-title-create{{/tr}}
      {{/if}}
      </th>
    </tr>
    <tr>
      <th>{{mb_label object=$route field="sender_class"}}</th>
      <td>
        <select class="{{$route->_props.sender_class}}" name="sender_class">
          {{foreach from=$list_sender item=_sender}}
            <option value="{{$_sender|getShortName}}" {{if $route->sender_class == $_sender|getShortName}}selected{{/if}}>
              {{tr}}{{$_sender|getShortName}}{{/tr}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label class=$route field="sender_id"}}</th>
      <td>
        <input class="{{$route->_props.sender_id}}" type="hidden" name="sender_id" value="{{if $route->_ref_sender}}{{$route->_ref_sender->_id}}{{/if}}">
        <input type="text" class="autocomplete" name="sender_id_autocomplete"
               value="{{if $route->_ref_sender}}{{$route->_ref_sender->_view}}{{/if}}">
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$route field="receiver_class"}}</th>
      <td>
        <select class=" {{$route->_props.receiver_class}}" name="receiver_class">
          {{foreach from=$list_receiver item=_receiver}}
            <option value="{{$_receiver|getShortName}}" {{if $route->receiver_class == $_receiver|getShortName}}selected{{/if}}>
              {{tr}}{{$_receiver|getShortName}}{{/tr}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label class=$route field="receiver_id"}}</th>
      <td>
        <input class="{{$route->_props.receiver_id}}" type="hidden" name="receiver_id" value="{{if $route->_ref_receiver}}{{$route->_ref_receiver->_id}}{{/if}}">
        <input type="text" class="autocomplete" name="receiver_id_autocomplete"
               value="{{if $route->_ref_receiver}}{{$route->_ref_receiver->_view}}{{/if}}">
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$route field="active"}}</th>
      <td>{{mb_field object=$route field="active"}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$route field="description"}}</th>
      <td>{{mb_field object=$route field="description"}}</td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        {{if $route->_id}}
          <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="confirmDeletion(this.form, {objName:'{{$route->_view|smarty:nodefaults|JSAttribute}}'}, Control.Modal.close)">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>