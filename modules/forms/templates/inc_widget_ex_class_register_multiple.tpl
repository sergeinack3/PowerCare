{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=cssStyle value=""}}
{{mb_default var=alternativeButton value=false}}
{{mb_default var=event_name value=''}}
{{mb_default var=object_class value=''}}

{{if "forms"|module_active}}
  {{unique_id var=uid}}
  <script>
    ExObject.registerFormItem("{{$object->_id}}", "ex_class-{{$uid}}", '{{$event_name}}', '{{$object_class}}');

    {{if $alternativeButton}}
    ExObject.alternativeButton = true;
    {{else}}
    ExObject.alternativeButton = false;
    {{/if}}
  </script>
  <div id="ex_class-{{$uid}}" style="{{$cssStyle}}" class="not-printable"></div>
{{/if}}
