{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=_type value=""}}
{{mb_default var=_host_field value=""}}
{{mb_default var=_spec value=""}}
{{mb_default var=trad value=false}}
{{mb_default var=layout_editor value=false}}

{{unique_id var=uid}}

{{if $_field == "_view" || $_field == "_shortview"}}
  <span class="{{if !$ex_class->pixel_positionning}} draggable {{/if}} hostfield value overlayed"
        data-field="{{$_field}}"
        data-type="value"
        data-ex_group_id="{{$ex_group_id}}"
        data-ex_class_id="{{$ex_class->_id}}"
        data-host_class="{{$_class}}"
        {{if $_host_field}}
          data-field_id="{{$_host_field->_id}}"
        {{/if}}
        ondblclick="ExClassHostField.del(this); Event.stop(event);"
        onclick="ExClass.focusResizable(event, this)"
  >
    {{if $layout_editor && $ex_class->pixel_positionning}}
      <button class="add compact me-secondary" type="button" onclick="ExClassHostField.create(this.up('.hostfield'))">
        {{if $_field == "_view"}}Vue{{else}}Vue courte{{/if}}
      </button>
    {{else}}
      <span style="position: relative;">
        <span class="field-name" style="display: none;">{{tr}}{{$_class}}{{/tr}} - </span>
        {{if $_field == "_view"}} Vue {{else}} Vue courte {{/if}}
        <div class="overlay" onmouseover="ObjectTooltip.createDOM(this, 'container-host_field-{{$uid}}');"></div>
      </span>
    {{/if}}
  </span>
{{else}}
  {{if !$_type || $_type == "label"}}
    <span class="{{if !$ex_class->pixel_positionning}} draggable {{/if}} hostfield label overlayed"
          data-field="{{$_field}}"
          data-type="label"
          data-ex_group_id="{{$ex_group_id}}"
          data-ex_class_id="{{$ex_class->_id}}"
          data-host_class="{{$_class}}"
          {{if $_host_field}}
            data-field_id="{{$_host_field->_id}}"
          {{/if}}
          ondblclick="ExClassHostField.del(this); Event.stop(event);"
          onclick="ExClass.focusResizable(event, this)"
    >
      {{if $layout_editor && $ex_class->pixel_positionning}}
        <button class="add compact me-secondary" type="button" onclick="ExClassHostField.create(this.up('.hostfield'))">
          libellé
        </button>
      {{else}}
      <span style="position: relative;">
        <span class="field-name" style="display: none;">
          {{if !$trad}}
            {{$_spec}}
          {{else}}
            {{tr}}{{$_class}}-{{$_field}}{{/tr}}
          {{/if}}
        </span>
        [libellé]
        <div class="overlay"></div>
      </span>
      {{/if}}
    </span>
  {{/if}}

  {{if !$_type || $_type == "value"}}
    <span class="{{if !$ex_class->pixel_positionning}} draggable {{/if}} hostfield value overlayed"
          data-field="{{$_field}}"
          data-type="value"
          data-ex_group_id="{{$ex_group_id}}"
          data-ex_class_id="{{$ex_class->_id}}"
          data-host_class="{{$_class}}"
          {{if $_host_field}}
            data-field_id="{{$_host_field->_id}}"
          {{/if}}
          ondblclick="ExClassHostField.del(this); Event.stop(event);"
          onclick="ExClass.focusResizable(event, this)"
    >
      {{if $layout_editor && $ex_class->pixel_positionning}}
        <button class="add compact me-secondary" type="button" onclick="ExClassHostField.create(this.up('.hostfield'))">
          valeur
        </button>
      {{else}}
        <span style="position: relative;">
          <span class="field-name" style="display: none;">
            {{if !$trad}}
              {{$_spec}}
            {{else}}
              {{tr}}{{$_class}}-{{$_field}}{{/tr}}
            {{/if}}
          </span>
          [valeur]
          <div class="overlay" onmouseover="ObjectTooltip.createDOM(this, 'container-host_field-{{$uid}}');"></div>
        </span>
      {{/if}}
    </span>
  {{/if}}

  {{if !$_type}}
    <span class="field-name">
      {{if !$trad}}
        {{$_spec}}
      {{else}}
        {{tr}}{{$_class}}-{{$_field}}{{/tr}}
      {{/if}}
    </span>
  {{/if}}
{{/if}}

<div id="container-host_field-{{$uid}}" style="display: none;">
  {{if $_host_field}}
    {{tr}}{{$_host_field->host_class}}{{/tr}} :

    {{if $_host_field->_field === '_view'}}
      Vue
    {{elseif $_host_field->_field === '_shortview'}}
      Vue courte
    {{else}}
      {{tr}}{{$_host_field->host_class}}-{{$_host_field->_field}}{{/tr}}
    {{/if}}

    <button type="button" class="edit" onclick="ExClassHostField.edit('{{$_host_field->_id}}');">
      {{tr}}common-action-Edit{{/tr}}
    </button>
  {{/if}}
</div>
