{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $from_field}}
  {{assign var=from_date value=`$object->$from_field`}}
{{else}}
  {{assign var=from_date value=""}}
{{/if}}
{{if $to_field}}
  {{assign var=to_date value=`$object->$to_field`}}
{{else}}
  {{assign var=to_date value=""}}
{{/if}}

{{if $from_field && $from_date}} 
  {{if $to_field && $to_date}}
    {{if $from_date != $to_date}}
      {{tr var1=$from_date|date_format:$conf.date var2=$to_date|date_format:$conf.date}}common-From %s to %s{{/tr}}
    {{else}}
      {{mb_value object=$object field=$from_field}}
    {{/if}}
  {{else}}
    {{tr}}common-Since{{/tr}} {{mb_value object=$object field=$from_field}}
  {{/if}}
    
{{else}}
  {{if $to_field && $to_date}}
    {{tr}}common-Until{{/tr}} {{mb_value object=$object field=$to_field}}
  {{/if}}
{{/if}}


