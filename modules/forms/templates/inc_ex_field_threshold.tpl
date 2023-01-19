{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=value value=null}}
{{mb_default var=title value=null}}

<i class="fas fa-thermometer-{{if $threshold == "low"}}empty{{else}}full{{/if}} fa-lg"
   style="color: {{if $threshold == "low"}}cornflowerblue{{else}}red{{/if}}" 
   {{if $title != "none"}}
     title="{{if $value !== null}}Valeur: {{$value}}{{else}}{{tr}}CExClassField-result_threshold_{{$threshold}}{{/tr}}{{/if}}"
   {{/if}}></i>