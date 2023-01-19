{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=cssStyle value=""}}
{{mb_default var=form_name value=""}}
{{mb_default var=alternativeButton value=false}}

{{if "forms"|module_active}}
  {{unique_id var=uid}}
  <script>
    Main.add(function(){
      ExObject.register("ex_class-{{$uid}}", {
        object_guid: "{{$object->_guid}}",
        event_name: "{{$event_name}}",
        title: "{{$object->_view|JSAttribute}}",
        form_name: "{{$form_name}}"
      });

      {{if $alternativeButton}}
        ExObject.alternativeButton = true;
      {{else}}
        ExObject.alternativeButton = false;
      {{/if}}
    });
  </script>
  <div id="ex_class-{{$uid}}" style="{{$cssStyle}}" class="not-printable"></div>
{{/if}}
