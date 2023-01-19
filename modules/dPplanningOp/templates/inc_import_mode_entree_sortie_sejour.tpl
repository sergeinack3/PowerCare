{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>Importation des {{tr}}{{$mode_class}}{{/tr}}</h2>

<div class="small-info">
  Veuillez indiquez les champs suivants (code, libelle, mode, actif) dans un fichier CSV (<strong>au format ISO</strong>)
  dont les champs sont séparés par <strong>;</strong> et les textes par <strong>"</strong>, la première ligne étant ignorée.
</div>

<form method="post" name="import" enctype="multipart/form-data">
  <input type="hidden" name="m" value="{{$m}}" />

  <input type="hidden" name="MAX_FILE_SIZE" value="4096000" />
  <input type="file" name="import" />

  <button type="submit" class="submit">{{tr}}Save{{/tr}}</button>
</form>