{{*
 * @package Mediboard\eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("tools_purge_files");
    Calendar.regField(form.elements.start_date);
    Calendar.regField(form.elements.end_date);
  });

  function automatic_purge_files() {
    var form = getForm("tools_purge_files");

    if (!$V(form["continue"])) {
      return;
    }
    form.onsubmit();
  }

</script>

<form name="tools_purge_files" method="get" action="?" onsubmit="return onSubmitFormAjax(this, null, 'result_purge_files')">
  <input type="hidden" name="m" value="{{$m}}" />
  <input type="hidden" name="a" value="purge_multiple_files" />
  <input type="hidden" name="reset" value="" />

  <table class="main form">
    <tr>
      <th>Essai à blanc</th>
      <td>
        <input type="checkbox" name="blank" checked />
      </td>
    </tr>
    <tr>
      <th>Limite</th>
      <td>
        <input type="number" name="limit" value="10" />
      </td>
    </tr>
    <tr>
      <th>Nom du fichier à chercher</th>
      <td>
        <input type="text" name="name" value="Labo_REPORTPDF"/>
      </td>
    </tr>
    <tr>
      <th>Date de début</th>
      <td>
        <input class="dateTime notNull" type="hidden" name="start_date" value="{{$_date}}" />
      </td>
    </tr>
    <tr>
      <th>Date de fin</th>
      <td>
        <input class="dateTime notNull" type="hidden" name="end_date" value="{{$_date}}" />
      </td>
    </tr>
    <tr>
      <th class="narrow">Automatique</th>
      <td><input type="checkbox" name="continue" /></td>
    </tr>
    <tr>
      <td colspan="2">
        <button type="submit" class="fas fa-sync" onclick="this.form.elements.reset.value = '0'">
          Purger les fichiers
        </button>
        <button type="submit" class="cancel me-secondary" onclick="this.form.elements.reset.value = '1'">
          Réinitialiser le pas
        </button>
      </td>
    </tr>
  </table>
</form>


<div id="result_purge_files" style="margin-top : 15px; margin-bottom: 15px;">
  {{tr}}Result{{/tr}} :
</div>
