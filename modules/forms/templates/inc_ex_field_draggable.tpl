{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="{{if !$ex_class->pixel_positionning}} draggable {{/if}} {{$_type}} overlayed"
     data-type="{{$_type}}" 
     data-field_id="{{$_field->_id}}" 
     ondblclick="ExField.edit({{$_field->_id}}); Event.stop(event);"
     onclick="ExClass.focusResizable(event, this)"
     unselectable="on"
     onselectstart="return false;"
  >
  <div style="position: relative; height: 100%;">
    {{if $_type == "field"}}
      {{if !$ex_class->pixel_positionning || !$_field->show_label}}
        <div class="field-info" style="display: none;">{{if $_field->_locale}}{{$_field->_locale}}{{else}}{{$_field->name}}{{/if}}</div>
      {{/if}}
      <div class="field-content">
        {{mb_include module=forms template=inc_ex_object_field ex_field=$_field mode=layout form="form-grid-layout"}}
      </div>
    {{else}}
      {{mb_label object=$ex_class->_ex_object field=$_field->name}}
    {{/if}}
    <div class="overlay"></div>
  </div>
</div>