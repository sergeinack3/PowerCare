{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $occupation < 80}}
  {{assign var="backgroundClass" value="normal"}}
{{elseif $occupation < 100}}
  {{assign var="backgroundClass" value="booked"}}
{{else}}
  {{assign var="backgroundClass" value="full"}}
{{/if}} 

{{if $occupation > -1}}
  <script type="text/javascript">
    {{if $view == 'old_dhe'}}
      OccupationServices.tauxOccupation = {{$occupation}};
    {{else}}
      DHE.sejour.setOccupation({{$occupation}});
    {{/if}}
  </script>
  <div class="progressBar">
    <div class="bar {{$backgroundClass}}" style="width: {{$pct}}%;"></div>
    <div class="text" style="text-align: center">{{$occupation|string_format:"%.0f"}} %</div>
  </div>
{{else}}
  {{if $view == 'dhe'}}
    <script type="text/javascript">
      DHE.sejour.setOccupation(-1);
    </script>
  {{/if}}
  <div class="empty">{{tr}}Unavailable{{/tr}}</div>
{{/if}}