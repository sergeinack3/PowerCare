{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $owner_id && $object->_class === "CMedecin"}}
  {{mb_script module=patients script=medecin ajax=$ajax}}
{{/if}}

<script>
  Main.add(function () {
    var form = getForm('search_salutations');
    form.onsubmit();
  });
</script>

<h2 style="text-align: center;">
  {{if $object->_id}}
  <span onmouseover="ObjectTooltip.createEx(this, '{{$object->_guid}}');">
    {{tr}}{{$object->_class}}{{/tr}} : {{$object}}
  </span>
  {{else}}
    {{tr}}{{$object->_class}}{{/tr}}
  {{/if}}
</h2>

<hr />

<form name="search_salutations" method="get" onsubmit="return onSubmitFormAjax(this, null, 'salutations_results')">
  <input type="hidden" name="m" value="patients" />
  <input type="hidden" name="a" value="ajax_manage_salutations" />
  <input type="hidden" name="object_class" value="{{$object->_class}}" />
  {{if $object->_id}}
    <input type="hidden" name="object_id" value="{{$object->_id}}" />
  {{else}}
    <input type="hidden" name="owner_id" value="{{$owner_id}}" />
  {{/if}}
</form>

<div id="salutations_results"></div>