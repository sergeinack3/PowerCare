{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=from_date value=$from|date_format:$conf.date}}
{{assign var=to_date   value=$to|date_format:$conf.date}}

{{if $from_date != $to_date}}
  {{if $from_date}}
		{{if !$to_date}} {{tr}}date.From_long{{/tr}} {{else}} {{tr}}date.from{{/tr}} {{/if}}
	  {{$from_date}}
	{{/if}}
	
  {{if $to_date}}
	  {{if !$from_date}} {{tr}}date.To_long{{/tr}} {{else}} {{tr}}date.to{{/tr}} {{/if}}
	  {{$to_date}}
  {{/if}}
{{elseif $from_date}}
	{{tr}}The{{/tr}} {{$from_date}}
{{/if}}
