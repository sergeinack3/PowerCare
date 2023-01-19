{{*
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=multi_ouverture value=false}}
{{if $date_checklist|date_format:$conf.date == $date|date_format:$conf.date}}
  {{assign var=multi_ouverture value=true}}
{{/if}}
{{mb_default var=sspi_id value=null}}
<div style="float: right;text-align: center;">
  <button class="checklist" type="button"
          onclick="EditCheckList.edit('{{$object_id}}', '{{$date}}', '{{$type}}', '{{$multi_ouverture}}', '{{$sspi_id}}');">
    {{tr}}CDailyCheckList._type.{{$type}}{{/tr}}
  </button>
  {{if $date_checklist}}
    <div class="info">
      {{tr}}CDailyCheckList.last_validation{{/tr}}: {{$date_checklist|date_format:$conf.date}}
    </div>
  {{/if}}
</div>