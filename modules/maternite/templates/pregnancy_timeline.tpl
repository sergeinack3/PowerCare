{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite script=timeline_implement ajax=$ajax}}

<script>
  Main.add(function () {
    TimelineImplement.pregnancy_id = '{{$pregnancy_id}}';
    TimelineImplement.refreshResume([], '{{$pregnancy_id}}');
  })
</script>

<div id="pregnancy_main_timeline"></div>
