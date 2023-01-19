{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$categorys item=_category}}
    <li id="{{$_category->_id}}" data-id="{{$_category->_id}}">
      <strong class="view">{{$_category->_view|emphasize:$keywords}}</strong>
      {{if $_category->code}}
        <div style="font-style: italic;">{{$_category->code}}</div>
      {{/if}}
    </li>
  {{foreachelse}}
    <li>
    <span class="informal">
      <span class="view"></span>
      <span style="font-style: italic;">{{tr}}No result{{/tr}}</span>
    </span>
    </li>
  {{/foreach}}
</ul>