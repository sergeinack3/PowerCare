{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning ajax=1}}

<script>
  Main.add(function() {
    ViewPort.SetAvlHeight('planning_interv', 1.0);

    var nb_hours = parseInt('{{$nb_hours}}');

    $('planningWeek').setStyle({height: ($('planning_interv').getHeight() * 20 / nb_hours) + 'px'});
  });
</script>

<div id="planning_operations">
  {{mb_include module=system template=calendars/vw_week}}
</div>

<form name="alterOp" method="post">
  <input type="hidden" name="m" value="planningOp" />
  <input type="hidden" name="dosql" value="do_planning_aed" />
  <input type="hidden" name="operation_id" />
  <input type="hidden" name="_move" />
</form>

<form name="moveOp" method="post">
  <input type="hidden" name="m" value="bloc" />
  <input type="hidden" name="dosql" value="do_move_operation" />
  <input type="hidden" name="operation_id" />
  <input type="hidden" name="plageop_id" />
  <input type="hidden" name="date" value="{{$date}}" />
  <input type="hidden" name="chir_id" value="{{$chir_id}}" />
  <input type="hidden" name="_move" value="movePlage" />
</form>

<script>
  Main.add(function() {
    var planning = window["planning-{{$planning->guid}}"];

    planning.onMenuClick = MultiSalle.onMenuClick;
  });
</script>