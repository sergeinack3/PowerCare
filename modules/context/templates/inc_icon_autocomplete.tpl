{{*
 * @package Mediboard\Context
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  <li data-url="url" data-name ="URL">
    <span>{{tr}}URL{{/tr}}</span>
  </li>
  {{foreach from=$list_icon key=_name item=_icon}}
    <li data-url="{{$_icon}} fa-{{$_name}}" data-name ="{{$_name}}">
      <span class="compact" style="float: right">
        {{$_name}}
      </span>
      <i class="{{$_icon}} fa-{{$_name}}" style="width: 20px"></i>
    </li>
  {{/foreach}}
</ul>
