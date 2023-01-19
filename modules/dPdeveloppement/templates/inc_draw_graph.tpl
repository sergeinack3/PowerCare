{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var graph = {{$graph|@json}};
    var backs = {{$backs|@json}};
    var backs_name = {{$backs_name|@json}};
    var opt = {
      hierarchy_sort: "{{$hierarchy_sort}}",
      show_hover: "{{$show_hover}}"
    };
    var inv_class = {{$inv_class|@json}};
    dataModel.prepareGraph(graph, backs, backs_name, inv_class, opt);
  });
</script>