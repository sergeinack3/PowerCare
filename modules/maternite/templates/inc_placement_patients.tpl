{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=urgences  script=drag_patient ajax=1}}
{{mb_script module=maternite script=tdb          ajax=1}}

<script>
  Main.add(function () {
    ChoiceLit.vue_hospi = 1;
    ChoiceLit.field_box_id = "_new_lit_id";
    ChoiceLit.isMater = 1;
  });
</script>

{{assign var=module_etiquette_pat value="maternite"}}
{{assign var=module               value=hospi}}

{{if $object|instanceof:'Ox\Mediboard\Bloc\CBlocOperatoire'}}
  {{assign var=module_etiquette_pat value="bloc"}}
  {{assign var=bloc                 value=$object}}
  {{assign var=name_grille          value=$bloc->nom}}
  {{assign var=module               value=bloc}}
{{else}}
  {{assign var=service     value=$object}}
  {{assign var=name_grille value=$service->nom}}
{{/if}}

<div class="grille">
  <table class="main" id="table_grille">
    {{mb_include module=$module template=inc_grille_topologie module_etiquette_pat=$module_etiquette_pat name_grille=$name_grille}}
  </table>
</div>