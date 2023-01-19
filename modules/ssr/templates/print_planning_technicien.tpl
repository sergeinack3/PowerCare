{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning}}
{{mb_script module=ssr script=planification}}

<script>
  Main.add(function(){
    Planification.current_m = "{{$m}}";
    PlanningTechnicien.show("{{$kine_id}}", "{{$surveillance}}", null, 800, false, true, true, {{$current_day}});
  });
</script>
<button type="button" class="print not-printable" style="float:right;" onclick="window.print();">
{{tr}}Print{{/tr}}
</button>

<div id="planning-technicien"></div>
