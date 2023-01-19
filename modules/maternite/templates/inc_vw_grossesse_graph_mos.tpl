{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite script=dossierMater ajax=1}}

<script>
  Main.add(function () {
    DossierMater.invokeGraphMosContent('{{$grossesse_id}}', '{{$list_graph}}');
  });
</script>

<table class="main" id="graph_mos_container">
  <tbody class="viewported">
  <tr>
    {{foreach item=_graph from=$array_graph name=graph_loop}}
    {{if !$smarty.foreach.graph_loop.first && $smarty.foreach.graph_loop.iteration %2 == 1}}
  </tr>
  <tr>
    {{/if}}
    <td class="viewport width50" id="graph_mos_container_{{$smarty.foreach.graph_loop.iteration}}"></td>
    {{/foreach}}
    {{if $array_graph|count %2 == 1}}
      <td></td>
    {{/if}}
  </tr>
  </tbody>
</table>