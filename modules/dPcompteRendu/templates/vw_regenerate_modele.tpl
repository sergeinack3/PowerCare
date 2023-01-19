{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  nextRegeneration = function () {
    var form = getForm('regenerate-modele');

    if (form && $V(form.elements.continue)) {
      form.onsubmit();
    }
  }
</script>

<form name="regenerate-modele" method="post" onsubmit="return onSubmitFormAjax(this, null, 'result-regenerate-modele')">
  <input type="hidden" name="m" value="dPcompteRendu"/>
  <input type="hidden" name="dosql" value="do_regenerate_modele"/>

  <table class="main form">
    <tr>
      <td colspan="2" class="button">Nombre d'aperçus à régénérer : {{$files_empty}}</td>
    </tr>

    <tr>
      <th><label for="start">{{tr}}Start{{/tr}}</label></th>
      <td><input type="text" name="start" value="0"/></td>
    </tr>

    <tr>
      <th><label for="step">{{tr}}Step{{/tr}}</label></th>
      <td><input type="text" name="step" value="10"/></td>
    </tr>

    <tr>
      <th><label for="continue">{{tr}}common-Automatic{{/tr}}</label></th>
      <td><input type="checkbox" name="continue" value="1"/></td>
    </tr>

    <tr>
      <td colspan="2" class="button">
        <button type="submit" class="change">Régénérer</button>
      </td>
    </tr>
  </table>
</form>

<div id="result-regenerate-modele"></div>