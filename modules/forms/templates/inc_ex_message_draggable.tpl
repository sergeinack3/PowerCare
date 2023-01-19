{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="{{if !$ex_class->pixel_positionning}} draggable {{/if}} {{$_type}} overlayed"
     data-type="{{$_type}}"
     data-message_id="{{$_field->_id}}"
     ondblclick="ExMessage.edit({{$_field->_id}}); Event.stop(event);"
     onclick="ExClass.focusResizable(event, this)"
     unselectable="on"
     onselectstart="return false;"
  >
  <div style="position: relative;">
    {{if $_type == "message_title"}}
      <div class="field-info" style="display: none;">{{$_field->title}}</div>
      <div class="field-content">{{$_field->title}}</div>
    {{else}}
      {{mb_include module=forms template=inc_ex_message _message=$_field}}
    {{/if}}
    <div class="overlay"></div>
  </div>
</div>