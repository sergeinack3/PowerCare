{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$trad key=_key item=_trad}}
    <li id="autocomplete-{{$_key}}" data-string="{{$_key}}" data-locale="{{$_trad.val}}">
      {{$_trad.key|smarty:nodefaults}}<br/>
      <strong>{{$_trad.val|smarty:nodefaults}}</strong>
    </li>
  {{foreachelse}}
    <li>
      <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
    </li>
  {{/foreach}}
</ul>