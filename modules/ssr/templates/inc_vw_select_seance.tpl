{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<select name="seance_collective_id" style="display: none;" onchange="onchangeSeance(this.value);">
  <option value="">{{tr}}CEvenementSSR-new_seance_collective{{/tr}}</option>
  {{foreach from=$seances item=_seance}}
    <option value="{{$_seance->_id}}">
      {{mb_value object=$_seance field=debut}} - {{mb_value object=$_seance field=duree}}
      {{tr}}common-noun-minutes-court{{/tr}}
    </option>
  {{/foreach}}
</select> 