{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul class="treeview">
{{foreach from=$component.children item=_child}}
  <li class="address">
    <input type="checkbox" name="address" value="{{$_child.fullpath}}" />
    <span class="field-name">{{$_child.name}}</span> (<span class="type">{{$_child.datatype}}</span>)

    {{if $_child.children|@count}}
      {{mb_include template="inc_hl7v2_transformation_group" component=$_child}}
    {{/if}}
  </li>
{{/foreach}}
</ul>