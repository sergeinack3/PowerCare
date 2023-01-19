{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=value value=$object->$field}}
{{assign var=field_id value="`$object->_guid`-$field"}}

{{mb_default var=show_label value=1}}
{{mb_default var=onchange value="this.form.onsubmit();"}}

{{if $value}}
  <input type="hidden" name="user_id" value="" />

  {{if $show_label}}
  <strong>
    {{if $object|instanceof:'Ox\Mediboard\Urgences\CRPU'}}
      {{mb_label object=$object field=$field}}
    {{else}}
      <label title="{{tr}}{{$object->_class}}-{{$type_attente}}-{{$field}}-desc{{/tr}}">
        {{tr}}{{$object->_class}}-{{$type_attente}}-{{$field}}-court{{/tr}}
      </label>
    {{/if}}
  </strong>
  {{/if}}

  <span id="{{$field_id}}-value">
    {{mb_value object=$object field=$field date=$rpu->_ref_sejour->entree|iso_date}}
  </span>

  <span style="white-space: nowrap;">
    <span id="{{$field_id}}-field" style="display: none;">
      {{mb_field object=$object field=$field form=$form register=true onchange=$onchange}}
    </span>

    <button class="edit notext" type="button" onclick="$('{{$field_id}}-value').hide(); $('{{$field_id}}-field').show(); $(this).hide();">
      {{tr}}Modify{{/tr}}
    </button>

    <button class="cancel notext" type="button" onclick="this.form.{{$field}}.value=''; {{$onchange}}">
      {{tr}}Cancel{{/tr}}
    </button>
  </span>
{{else}}
  {{if $type_attente == "bio"}}<input type="hidden" name="user_id" value="{{$app->user_id}}" />{{/if}}
  <input type="hidden" name="{{$field}}" value="" />
  <button class="submit {{if !$show_label}}notext{{/if}}" type="button" onclick="this.form.{{$field}}.value='current'; {{$onchange}}">

    {{if $object|instanceof:'Ox\Mediboard\Urgences\CRPU'}}
      {{tr}}{{$object->_class}}-{{$field}}{{/tr}}
    {{else}}
      {{tr}}{{$object->_class}}-{{$type_attente}}-{{$field}}{{/tr}}
    {{/if}}
  </button>
{{/if}}
  