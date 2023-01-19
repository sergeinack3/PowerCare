{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$document_items item=_list_items key=_cat_name}}
  {{if $_cat_name}}
    <tr>
      <th class="category">{{$_cat_name}}</th>
    </tr>
  {{/if}}

  {{foreach from=$_list_items item=_item}}
    <tr>
      <td>
        <a href="embed:{{$_item->_class}},{{$_item->_id}}">{{$_item}}</a>
      </td>
    </tr>
  {{/foreach}}
{{/foreach}}
