{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=maternite script=naissance ajax=1}}

<script>
  Main.add(Naissance.reloadNaissances.curry(null, "{{$sejour_id}}"));
</script>

<fieldset class="me-margin-bottom-12">
  <legend>{{tr}}CPatient-back-naissances{{/tr}}</legend>

  <div id="naissance_area"></div>
</fieldset>