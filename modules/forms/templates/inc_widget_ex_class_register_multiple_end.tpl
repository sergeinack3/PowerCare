{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=object_class value=''}}
{{mb_default var=event_name value=''}}

{{if "forms"|module_active}}
  {{mb_default var=callback value=null}}

  <script>
    ExObject.displayRegisteredFormItems("{{$object_class}}", "{{$event_name}}" {{if $callback}}, null, {{$callback}} {{/if}});
  </script>
{{/if}}
