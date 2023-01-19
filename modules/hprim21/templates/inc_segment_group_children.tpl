{{*
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{foreach from=$segment_group->children item=_child}}
  <li>
    {{if $_child|instanceof:'Ox\Interop\Hl7\CHL7v2Segment'}}
      <strong class="field-description">{{$_child->description}}</strong> 
      <span class="type">{{$_child->name}}</span>
      
      {{if $_child->fields|@count}}
        <ul>
          {{foreach from=$_child->fields key=_field_pos item=_field}}
            {{assign var=_field_pos value=$_field_pos+1}}
            
            <li>
              <span class="field-name">{{$_field->getPathString(".")}}</span>
              <span class="field-description">{{$_field->description}}</span>
              <span class="type">{{$_field->getTypeTitle()}}</span>
              
              {{if $_field->table}}
                <span class="table">{{$_field->table}}</span>
              {{/if}}
              
              {{if $_field->items|@count}}
                <ul>
                  {{foreach from=$_field->items key=_i item=_item}}
                    <li>
                      {{mb_include module=hl7 template=inc_tree_field_item component=$_item}}
                    </li>
                  {{/foreach}}
                </ul>
              {{/if}}
            </li>
          {{/foreach}}
        </ul>
      {{/if}}
    {{else}}
      {{$_child->name}}
      {{if $_child->children|@count}}
        <ul>
          {{mb_include module=hprim21 template=inc_segment_group_children segment_group=$_child}}
        </ul>
      {{/if}}
    {{/if}}
  </li>
{{/foreach}}
