{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=from_date value=$from|date_format:$conf.date}}
{{assign var=to_date   value=$to|date_format:$conf.date}}
{{assign var=today value=$dnow}}
{{assign var=from_dateD value=$from|iso_date}}
{{assign var=to_dateD value=$to|iso_date}}


{{if $from_date != $to_date}}   <!-- from date != to date -->

  <!-- FROM -->
  {{if $today == $from_dateD}}
    {{tr}}From_Today{{/tr}} {{$from|date_format:$conf.time}}
  {{else}}
    Du {{$from|date_format:$conf.datetime}}
  {{/if}}
  <!-- TO -->
  {{if $today == $to_dateD}}
    à {{tr}}Today{{/tr}} {{$to|date_format:$conf.time}}
  {{else}}
    au {{$to|date_format:$conf.datetime}}
  {{/if}}

{{elseif $from == $to}}       <!-- from dateTime == to dateTime -->
  {{if $today == $from_dateD}}
    {{tr}}From_Today{{/tr}}
  {{else}}
    Le {{$from_date}}
  {{/if}}
  à  {{$to|date_format:$conf.time}}

{{else}}
  {{if $today == $from_dateD}}
    {{tr}}Today{{/tr}}
  {{else}}
    Le {{$from_date}}
  {{/if}}
  de {{$from|date_format:$conf.time}} 
  à  {{$to|date_format:$conf.time}}
{{/if}}