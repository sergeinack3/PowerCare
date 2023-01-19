{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$list->_ref_items item=_list_item}}
    <li>
      {{$_list_item->name}} &mdash; 
      {{if $_list_item->code != ""}}{{$_list_item->code}}{{else}}<span class="empty">Aucune code</span>{{/if}}
    </li>
  {{/foreach}}
</ul>