{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
  {{foreach from=$ex_class_fields item=_field}}
    <li data-id="{{$_field->_id}}" data-view="{{$_field}}">{{$_field}}</li>
  {{/foreach}}
</ul>
