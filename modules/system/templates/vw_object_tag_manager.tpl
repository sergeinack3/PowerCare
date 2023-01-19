{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if $root}}
  <script type="text/javascript">
    Main.add(function(){
      MbObject.edit("CTag-0", {customValues: {object_class: {{$object_class|@json}}}});
    });
  </script>
  <table class="main layout" style="table-layout: fixed;">
    <tr>
      <td>
  <table class="main tbl treegrid">
    <tr>
      <th>
        Gestionnaire de tags pour : {{tr}}{{$object_class}}{{/tr}}
      </th>
    </tr>
{{/if}}

{{mb_default var=children value=$tree.children}}
{{mb_default var=parent value=null}}
{{mb_default var=level value=0}}
{{mb_default var=ancestors value=""}}

{{foreach from=$children item=_tag name=tree}}
  <tbody data-tag_id="{{$_tag.parent->_id}}"
         class="opened {{foreach from=","|explode:$ancestors item=_ancestor}}{{if $_ancestor}}tag-{{$_ancestor}} {{/if}}{{/foreach}}"
         {{if $parent}}data-parent_tag_id="{{$parent->_id}}"{{/if}}
         >
    <tr>
      <td>
        <button class="edit notext compact" style="float: right;" onclick="MbObject.edit('{{$_tag.parent->_guid}}')">{{tr}}Edit{{/tr}}</button>
        <span style="background: #{{$_tag.parent->color}}; float: right; display: inline-block; width: 10px; height: 16px; margin-right: 3px;"> </span>
        
        {{if $_tag.children|@count}}
          <a href="#1" style="margin-left: {{$level*18}}px; font-weight: normal;" class="tree-folding" onclick="$(this).up('tbody').toggleClassName('opened'); Tag.setNodeVisibility(this); return false;">
            {{$_tag.parent->name}}
          </a>
        {{else}}
          <span style="margin-left: {{$level*18}}px; padding-left: 16px;">{{$_tag.parent->name}}</span>
        {{/if}}
      </td>
    </tr>
  </tbody>
  
  {{mb_include module=system template=vw_object_tag_manager root=false parent=$_tag.parent children=$_tag.children level=$level+1 ancestors="$ancestors,`$_tag.parent->_id`"}}
{{/foreach}}

{{if $root}}
  {{if $children|@count == 0}}
    <tbody>
      <tr>
        <td colspan="2" class="empty">
          {{tr}}CTag.none{{/tr}}
        </td>
      </tr>
    </tbody>
  {{/if}}
  
  </table>
      </td>
      <td id="object-editor"></td>
    </tr>
  </table>
{{/if}}
