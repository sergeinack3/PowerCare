{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=display value="icon"}}

{{assign var=ex_object value=$link->_ref_ex_object}}
{{assign var=ex_class value=$ex_object->_ref_ex_class}}

{{if $display == "icon"}}
  <table class="layout table_icon_fileview" onmouseover="ObjectTooltip.createEx(this, '{{$ex_object->_class}}-{{$ex_object->_id}}')">
    <tr>
      <td style="text-align: center; height: 120px; vertical-align: middle;">
        <div class="icon_fileview" style="line-height: 90px;"
             ontouchend="ExObject.display('{{$ex_object->_id}}', '{{$ex_object->_ex_class_id}}', '{{$ex_object->object_class}}-{{$ex_object->object_id}}')"
             ondblclick="ExObject.display('{{$ex_object->_id}}', '{{$ex_object->_ex_class_id}}', '{{$ex_object->object_class}}-{{$ex_object->object_id}}')"
        >
          <span style="font-family: FontAwesome; font-size: 11pt;">
            &#xf0f7;
          </span>
        </div>
      </td>
    </tr>
    <tr>
      <td class="text item_name" style="text-align: center; vertical-align: top;">{{$ex_class->_icon_name}}</td>
    </tr>
  </table>
  {{mb_return}}
{{/if}}

<tr>
  <td class="narrow">
    <span style="font-family: FontAwesome; font-size: 11pt;">&#xf0f7;</span>
  </td>
  <td class="item_name" onmouseover="ObjectTooltip.createEx(this, '{{$ex_object->_class}}-{{$ex_object->_id}}')">
    {{$ex_class->name}}
  </td>
  <td>
    {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$ex_object->_ref_owner}}
  </td>
  <td style="width: 25%">
  </td>
  <td>
    <span onmouseover="ObjectTooltip.createEx(this, '{{$ex_object->_ref_object->_guid}}')" class="list_file_name">
      {{$ex_object->_ref_object}}
    </span>
  </td>
  <td class="narrow">
    {{mb_value object=$ex_object field=datetime_edit}}
  </td>
</tr>

