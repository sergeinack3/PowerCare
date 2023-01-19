{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$tags item=_tag}}
    <li data-tag="{{$_tag->getTag()}}">{{$_tag->getName()|emphasize:$keywords}}</li>
  {{foreachelse}}
    <li class="empty">{{tr}}common-No result{{/tr}}</li>
  {{/foreach}}
</ul>