{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=labo script=examen ajax=true}}

<script>
  Main.add(function() {
    Examen.refreshList($V(getForm('selectCatalogue').catalogue_labo_id));
  });
</script>

<!-- Sélection du catalogue -->
<form name="selectCatalogue" method="get">
  <label for="catalogue_labo_id" title="Selectionner le catalogue que vous désirez afficher">
    Catalogue courant
  </label>

  <select name="catalogue_labo_id" onchange="Examen.refreshList(this.value);">
    {{assign var="selected_id" value=""}}
    {{assign var="exclude_id" value=""}}
    {{foreach from=$listCatalogues item="_catalogue"}}
      {{mb_include module=labo template=options_catalogues}}
    {{/foreach}}
  </select>
</form>

<button type="button" class="new" onclick="Examen.edit();">{{tr}}CExamenLabo.create{{/tr}}</button>

<div id="list_examens"></div>