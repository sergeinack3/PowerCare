{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=field_name value="active"}}
{{mb_default var=onComplete value=""}}

<form name="edit-object-{{$object->_guid}}" method="post"
      onsubmit="return onSubmitFormAjax(this, function () { {{$onComplete}} });">
  {{mb_key object=$object}}
  {{mb_class object=$object}}
  {{mb_field object=$object field=$field_name hidden=true}}

  <a href="#1" onclick="toggleUpdate(this.up('form').elements.{{$field_name}});" style="display: inline-block; vertical-align: middle;">
    {{if $object->$field_name}}
      <i class="fa fa-toggle-on" style="color: #449944; font-size: large;"></i>
    {{else}}
      <i class="fa fa-toggle-off" style="font-size: large;"></i>
    {{/if}}
  </a>
</form>