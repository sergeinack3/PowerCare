{{*
 * @package Mediboard\Urgences
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div id="lit_urgence_topo" class="{{$button_name}} lit_bloque_urgences clit_bloque draggable me-margin-10" style="display: inline-block;">
    <strong>[{{tr}}CChambre-BLOCK A BED{{/tr}}]</strong>
</div>
<script>
    Main.add(function () {
        new Draggable($$('div#lit_urgence_topo.{{$button_name}}')[0], {revert: true});
    });
</script>

<div class="grille">
  <table class="main" id="table_grille">
    {{mb_include module=hospi template=inc_grille_topologie
       grille=$grilles.$name_grille
       listSejours=$listSejours.$name_grille
       exist_plan=$exist_plan.$name_grille}}
  </table>
</div>
