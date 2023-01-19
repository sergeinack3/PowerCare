{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning ajax=1}}

{{mb_include module=system template=calendars/vw_week}}

<script type="text/javascript">
  Main.add(function() {
    var planning = window["planning-{{$planning->guid}}"];

    planning.onMenuClick = function(action, object_id, element) {
      switch (action) {
        case 'edit':
          if (element.up().up().hasClassName('plage_planning')) {
            editPlageOp(object_id);
          }
          else if (element.up().up().hasClassName('tl_operation')) {
            editOperation(object_id);
          }
          break;
        case 'list':
          orderOperations(object_id);
          break;
        case 'print':
          printOperation(object_id)
          break;
      }
    };
  });
</script>