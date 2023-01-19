{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=from value=""}}
{{mb_default var=to   value=""}}
{{mb_default var=format value=$conf.date}}
{{assign var=from_date value=$from|date_format:$format}}
{{assign var=to_date   value=$to|date_format:$format}}

{{if $from}} 
  {{if $to}} 
    {{if $from_date != $to_date}}
      {{tr}}date.From{{/tr}} {{$from_date}}
      {{tr}}date.to{{/tr}} {{$to_date}}
    {{else}}
      {{tr}}The{{/tr}} {{$from_date}}
    {{/if}}
  {{else}}
	{{tr}}Since-long{{/tr}} {{$from_date}}
  {{/if}}
    
{{else}}
  {{if $to}} 
    {{tr}}date.To_long{{/tr}} {{$to_date}}
  {{/if}}
{{/if}}


