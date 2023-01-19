{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $component->props|instanceof:'Ox\Interop\Hl7\CHL7v2DataTypeComposite'}}
  {{if $component->children|@count > 0}}
    Item {{$_i+1}}
    <ul class="field-item">
      {{foreach from=$component->children key=i item=_child}}
        <li>
          {{mb_include module=hl7 template=inc_tree_component component=$_child}}
        </li>
      {{/foreach}}
    </ul>
  {{/if}}
{{else}}
  <span class="value">{{$component->data}}</span>
{{/if}}
