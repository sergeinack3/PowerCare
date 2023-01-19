{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    ViewPort.SetAvlHeight("planning_ressources", 1);
    $("planningWeek").setStyle({height : "{{$height_calendar}}px"});
  });
</script>

{{mb_include module=system template=calendars/vw_week}}