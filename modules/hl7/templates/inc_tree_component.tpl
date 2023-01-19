{{*
 * @package Mediboard\Hl7
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=segment value=$component->getSegment()}}

<span class="field-name {{if $component->invalid}}invalid{{/if}}">{{$component->getPathString(".")}}</span>
<span class="field-description">{{$component->description}}</span>
<span class="type">{{$component->getTypeTitle()}}</span>
            
{{if $component->table}}
  <span class="table">{{$component->table}}</span>
{{/if}}
  
{{if $component->props|instanceof:'Ox\Interop\Hl7\CHL7v2DataTypeComposite'}}
  {{if $component->children|@count}}
    <ul>
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