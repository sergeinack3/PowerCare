{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPplanningOp script=operation}}

<script>
  popupImport = function() {
    var url = new Url('dPplanningOp', 'libelle_import_csv');
    url.popup(800, 600, 'Import des libellés');
  };

  changePage = function(page) {
    $V(getForm('search_libelle').page, page);
  };
</script>

<script>
  Main.add(function() {
    getForm('search_libelle').onsubmit();
  });
</script>

<button type="button" class="new" onclick="Libelle.edit('0');">{{tr}}CLibelleOp-title-create{{/tr}}</button>
<button type="button" onclick="popupImport();" class="hslip">{{tr}}Import-CSV{{/tr}}</button>

<form name="search_libelle" action="?" method="get" onsubmit="return onSubmitFormAjax(this, null, 'results_libelle')">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="a" value="ajax_search_libelle" />
  <input type="hidden" name="page" value="0" onchange="this.form.onsubmit();"/>
  <table class="form">
    <tr>
      <th style="width: 50%;">{{mb_label class= CLibelleOp field=nom}}</th>
      <td>
        <input type="text" name="nom" value="">
        <button type="button" class="cancel notext" onclick="this.form.nom.value='';">{{tr}}Vider{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <td colspan="2" class="button">
        <button class="search">{{tr}}Filter{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>
<div id="results_libelle"></div>