{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$object_vars key=_view item=_value}}
  <button type="button" onclick="this.form.pattern.replaceInputSelection(this.value); this.form.pattern.fire('ui:change')"
          value="{{$_value}}" class="up">{{$_view}}</button>
{{/foreach}}