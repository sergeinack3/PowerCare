{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">Répartition des champs de formulaires, pour chaque formulaire associé à l'établissement courant.</div>

<form name="exclass-filter" method="get" data-loaded="" onsubmit="return onSubmitFormAjax(this, null, 'exclass-results');">
  <input type="hidden" name="m" value="forms" />
  <input type="hidden" name="a" value="ajax_vw_ex_class_stats" />

  <label>
    Nombre minimum
    <input type="text" size="2" name="param_min_count" value="{{$param_min_count}}" />
  </label>

  <button type="submit" class="stats">{{tr}}Display{{/tr}}</button>
</form>

<div id="exclass-results"></div>