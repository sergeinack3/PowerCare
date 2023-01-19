{{*
 * @package Mediboard\Astreintes
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=object value=""}}

{{if is_object($object)}}

  {{if $object->start|iso_date == $today}}
    {{tr}}Today{{/tr}}
    {{if $object->end|iso_date == $object->start|iso_date}}
      {{tr}}from{{/tr}}
    {{else}}
      au
    {{/if}}
    {{$object->start|date_format:$conf.time}}
  {{else}}
   Du {{mb_value object=$object field=start}}
  {{/if}}

  {{if $object->end|iso_date == $today}}
    {{if $object->end|iso_date == $object->start|iso_date}}
      {{tr}}to{{/tr}}
    {{/if}}
     {{$object->end|date_format:$conf.time}}
  {{else}}
   au {{mb_value object=$object field=end}}
  {{/if}}

{{else}}
  <div class="small-warning">{{tr}}CPlageCalendaire-no-object-for-template{{/tr}}</div>
{{/if}}