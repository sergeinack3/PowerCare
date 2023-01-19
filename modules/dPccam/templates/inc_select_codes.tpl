{{*
 * @package Mediboard\Ccam
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<option>Choisir le niveau suivant</option>
{{foreach from=$list item=_chap key=key_chap}}
  <option  value="{{$_chap.rank}}" data-code-pere="{{$key_chap}}">
    {{$_chap.rank}} - {{$_chap.text|lower}}
  </option>
{{/foreach}}