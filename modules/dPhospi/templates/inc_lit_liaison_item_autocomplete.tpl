{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$items_by_prestation item=_items key=_prestation_id}}
    <li style="background: #fdd;">
      {{assign var=prestation value=$prestations.$_prestation_id}}
      {{$prestation->nom}}
    </li>
    {{foreach from=$_items item=_item_prestation}}
      <li data-id="{{$_item_prestation->_guid}}" style="margin-left: 1em;">
        <div>{{$_item_prestation}}</div>
      </li>
    {{/foreach}}
  {{/foreach}}
</ul>