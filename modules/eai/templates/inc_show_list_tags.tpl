{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<select name="tag">
  {{foreach from=$tags item=_tag}}
    <option value="{{$_tag}}">{{$_tag}}</option>
  {{/foreach}}
</select>