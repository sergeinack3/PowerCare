{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editRelatedReceiverToCategory-{{$related_receiver->_guid}}" method="post" onsubmit="return onSubmitFormAjax(this, Control.Modal.close);">
  {{mb_class object=$related_receiver}}
  {{mb_key   object=$related_receiver}}
  <input type="hidden" name="receiver_class"    value="{{if $related_receiver->_ref_receiver}}{{$related_receiver->_ref_receiver->_class}}{{/if}}" />
  <input type="hidden" name="receiver_id"       value="{{if $related_receiver->_ref_receiver}}{{$related_receiver->_ref_receiver->id}}{{/if}}" />
  <input type="hidden" name="files_category_id" value="{{if $related_receiver->_id}}{{$related_receiver->files_category_id}}{{else}}{{$selected_category->_id}}{{/if}}" />
  <input type="hidden" name="type"              value="{{$related_receiver->type}}">

  <table class="form">
    <tr>
      {{if $related_receiver->_id}}
        <th class="title modify text" colspan="2">
            {{mb_include module=system template=inc_object_idsante400 object=$related_receiver}}
            {{mb_include module=system template=inc_object_history object=$related_receiver}}
            {{tr}}{{$related_receiver->_class}}-title-modify{{/tr}} '{{$related_receiver}}'
      {{else}}
        <th class="title me-th-new" colspan="2">
            {{tr}}{{$related_receiver->_class}}-title-create{{/tr}}
      {{/if}}
        </th>
    </tr>
    <tr>
      <th>{{mb_label class=$related_receiver field="receiver_id" }}</th>
      <td>
        <select name="_receiver" class="notNull"
                onchange="$V(this.form.elements.receiver_class, this.options[this.selectedIndex].get('receiver-class'));
                          $V(this.form.elements.receiver_id, this.options[this.selectedIndex].get('receiver-id'));
                          $V(this.form.elements.type, this.options[this.selectedIndex].dataset.type);">
          <option value="">&mdash;</option>
          {{foreach from=$available_receivers key=_available_receiver_type item=_available_receivers}}
            <optgroup label="{{tr}}module-{{$_available_receiver_type}}-court{{/tr}}">
              {{foreach from=$_available_receivers item=_available_receiver}}
                {{if $_available_receiver|@is_array}}
                  {{foreach from=$_available_receiver item=_available_receiver_sih_cabinet}}
                    <option data-receiver-id="{{$_available_receiver_sih_cabinet->_id}}" data-receiver-class="{{$_available_receiver_sih_cabinet->_class}}" value="{{$_available_receiver_sih_cabinet->_id}}" {{if $related_receiver->_ref_receiver->_id == $_available_receiver_sih_cabinet->_id}}selected{{/if}}>
                      {{$_available_receiver_sih_cabinet->_view}}
                    </option>
                  {{/foreach}}
                {{elseif is_string($_available_receiver)}}
                    <option data-type="{{$_available_receiver}}" value="{{$_available_receiver}}" {{if $related_receiver->type == $_available_receiver}}selected{{/if}}>{{tr}}CFilesCategoryToReceiver.type.{{$_available_receiver}}{{/tr}}</option>
                {{else}}
                  <option data-receiver-id="{{$_available_receiver->_id}}" data-receiver-class="{{$_available_receiver->_class}}" value="{{$_available_receiver->_id}}" {{if $related_receiver->_ref_receiver->_id == $_available_receiver->_id}}selected{{/if}}>
                    {{$_available_receiver->_view}}
                  </option>
                {{/if}}
              {{/foreach}}
            </optgroup>
          {{/foreach}}
        </select>
      </td>
    </tr>
    <tr>
      <th>{{mb_label object=$related_receiver field=active}}</th>
      <td>{{mb_field object=$related_receiver field=active}}</td>
    </tr>
    <tr>
      <th>{{mb_label object=$related_receiver field=description}}</th>
      <td>{{mb_field object=$related_receiver field=description}}</td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        {{if $related_receiver->_id}}
          <button type="submit" class="modify">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button"
                  onclick="confirmDeletion(this.form, {objName:'{{$related_receiver->_view|smarty:nodefaults|JSAttribute}}'}, Control.Modal.close)">{{tr}}Delete{{/tr}}</button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
