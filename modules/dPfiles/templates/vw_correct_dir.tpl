{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    var form = getForm('do-correct-files-dir');
    Calendar.regField(form.date_min);
    Calendar.regField(form.date_max);
  });

  submitCount = function (count) {
    var form = getForm('do-correct-files-dir');
    $V(form.count, count);
    form.onsubmit();
  };

  nextCorrection = function (start) {
    var form = getForm('do-correct-files-dir');
    $V(form.elements.start, start);

    if ($V(form.elements.continue)) {
      form.onsubmit();
    }
  }
</script>

<form name="do-correct-files-dir" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-correct-files-dir')">
  <input type="hidden" name="m" value="files"/>
  <input type="hidden" name="dosql" value="do_correct_files_dir"/>
  <input type="hidden" name="count" value="0"/>

  <table class="main form">
    <tr>
      <th><label for="old_path">Chemin vers les anciens fichiers</label></th>
      <td><input type="text" name="old_path" size="50"/></td>
    </tr>

    <tr>
      <th><label for="old_size">Ancienne taille des sous-dossiers</label></th>
      <td><input type="text" name="old_size"/></td>
    </tr>

    <tr>
      <th><label for="date_min">Date minimum de recherche</label></th>
      <td><input class="dateTime" type="hidden" name="date_min" value=""/></td>
    </tr>

    <tr>
      <th><label for="date_max">Date maximum de recherche</label></th>
      <td><input class="dateTime" type="hidden" name="date_max" value=""/></td>
    </tr>

    <tr>
      <th><label for="copy">Copier les fichiers au lieu de les déplacer</label></th>
      <td><input type="checkbox" name="copy" value="1" checked/></td>
    </tr>

    <tr>
      <th><label for="start">{{tr}}Start{{/tr}}</label></th>
      <td><input type="number" name="start" value="0"/></td>
    </tr>

    <tr>
      <th><label for="step">{{tr}}Step{{/tr}}</label></th>
      <td><input type="number" name="step" value="100"/></td>
    </tr>

    <tr>
      <th><label for="continue">Auto</label></th>
      <td><input type="checkbox" name="continue" value="1" checked/></td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="button" class="change" onclick="submitCount(1)">Compter les fichiers à corriger</button>
        <button type="button" class="change" onclick="submitCount(0)">Corriger</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-correct-files-dir"></div>
