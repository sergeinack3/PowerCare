{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{math assign=colspan equation="x+1" x=$columns|@count}}

{{if $root}}

  {{if $columns|is_array}}
    {{assign var=_columns value=","|implode:$columns}}
  {{else}}
    {{assign var=_columns value=""}}
  {{/if}}
  
  <button style="float: right;" class="tag-edit me-tertiary" type="button" onclick="Tag.manage('{{$object_class}}')">
    Gérer les tags
  </button>
  
  <table class="main tbl me-no-align me-no-border-radius-bottom" data-columns="{{$_columns}}" data-object_class="{{$object_class}}">
    <tr>
      <th colspan="{{$colspan}}" style="padding: 0;">
        <form name="filter-{{$object_class}}" method="get" action="?" onsubmit="return false">
          <input type="hidden" name="group_id" value="{{$group_id}}" />
          
          Filtrer:
          <label>
            Tag
            <input type="text" name="tag" onkeyup="Tag.filter(this)" style="width: 5em;" />
          </label>
          <button class="cancel notext me-margin-left-4" type="button" onclick="Tag.cancelFilter(this.form.tag)"
                  style="margin-left: -5px;">
            {{tr}}Cancel{{/tr}}
          </button>

          <label>
            Nom
            <input type="text" name="object_name" onkeyup="Tag.launchFilterObject(this)" style="width: 5em;" />
          </label>
          <button class="cancel notext me-margin-left-4" type="button"
                  onclick="Tag.cancelFilterObject(this.form.object_name)" style="margin-left: -5px;">
            {{tr}}Cancel{{/tr}}
          </button>
        </form>
      </th>
    </tr>
  </table>
  
  <table class="main tbl treegrid me-no-align me-no-border me-no-border-radius-top" data-columns="{{$_columns}}" data-object_class="{{$object_class}}">
{{/if}}

{{mb_default var=children value=$tree.children}}
{{mb_default var=parent value=null}}
{{mb_default var=level value=0}}
{{mb_default var=ancestors value=""}}

{{foreach from=$children item=_tag name=tree}}
  <tbody data-tag_id="{{$_tag.parent->_id}}"
         class="{{foreach from=","|explode:$ancestors item=_ancestor}}{{if $_ancestor}}tag-{{$_ancestor}} {{/if}}{{/foreach}}"
         {{if $parent}}data-parent_tag_id="{{$parent->_id}}"{{/if}}
         style="{{if !$root}}display: none;{{/if}}"
         data-name="{{$_tag.parent->name}}"
         data-deepness={{$_tag.parent->_deepness}}
         >
    <tr>
      <td colspan="{{$colspan}}">
        <a href="#unfold.{{$_tag.parent->_guid}}" style="margin-left: {{$level*18}}px; {{if $_tag.parent->color}}border-color: #{{$_tag.parent->color}};{{/if}}" 
           class="tree-folding" onclick="$(this).up('tbody').toggleClassName('opened'); Tag.setNodeVisibility(this); Tag.loadElements(this); return false;">
          {{$_tag.parent->name}}
        </a>
      </td>
    </tr>
  </tbody>
  
  {{mb_include module=system template=inc_object_tag_tree root=false parent=$_tag.parent children=$_tag.children level=$level+1 ancestors="$ancestors,`$_tag.parent->_id`"}}
{{/foreach}}

{{if $root}}
    <tbody data-tag_id="none-{{$object_class}}" class="tag-none" data-name="__none__">
      <tr>
        <td colspan="{{$colspan}}">
          <a href="#unfold.none" class="tree-folding" onclick="$(this).up('tbody').toggleClassName('opened'); Tag.setNodeVisibility(this); Tag.loadElements(this); return false;">
            Non classé
          </a>
        </td>
      </tr>
    </tbody>
  </table>
  
  <table class="object-list main tbl" style="display: none;">
  </table>
{{/if}}
