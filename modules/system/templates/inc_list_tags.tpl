{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_pagination current=$page step=$limit total=$total change_page=refreshTagList}}
<form name="merge_tags">
  <table class="tbl">
    <tr>
      {{if $tag->_can->edit && $tags|@count}}
        <th class="narrow"><button type="button" class="merge notext" onclick="doMerge(this.form);">{{tr}}Merge{{/tr}}</button></th>
      {{/if}}
      <th>{{mb_title object=$tag field=name}}</th>
      <th>{{mb_title object=$tag field=parent_id}}
      {{if $tag_parent->_id}}
        <a href="#" style="color:red; text-decoration: underline; display: inline" onclick="removeParent();">{{$tag_parent}}</a>
      {{/if}}</th>
      <th class="narrow">{{mb_title object=$tag field=_nb_items}}</th>
    </tr>
    {{foreach from=$tags item=_tag}}
      <tr>
        {{if $tag->_can->edit}}
          <td>
            <input type="checkbox" name="objects_id[]" value="{{$_tag->_id}}"/>
          </td>
        {{/if}}
        <td style="border-left:solid 10px {{if $_tag->color}}#{{$_tag->color}}{{else}}transparent{{/if}}">
          <a href="#{{$_tag->_id}}" onclick="editTag('{{$_tag->_id}}');">{{mb_value object=$_tag field=name}}</a>
        </td>
        <td style="vertical-align: middle">
          {{if $_tag->parent_id}}
            <i class="me-icon search me-primary" onclick="refreshTagList(null, '{{$_tag->parent_id}}');"></i>
            <a href="#{{$_tag->parent_id}}" style="display: inline" onclick="editTag('{{$_tag->parent_id}}');">{{$_tag->_ref_parent}}</a>
          {{/if}}
        </td>
        <td {{if !$_tag->_nb_items}}class="empty"{{/if}}>
          {{if !$_tag->_nb_items}}<button type="button" class="trash notext" style="float:right;" onclick="purgeTag('{{$_tag->_id}}', '{{$_tag->name}}');">{{tr}}Delete{{/tr}}</button>{{/if}}
          {{$_tag->_nb_items}}
        </td>
      </tr>
    {{foreachelse}}
      <tr>
        <td colspan="{{if $tag->_can->edit}}4{{else}}3{{/if}}" class="empty">{{tr}}CTag.none{{/tr}}</td>
      </tr>
    {{/foreach}}
  </table>
</form>
{{mb_include module=system template=inc_pagination current=$page step=$limit total=$total change_page=refreshTagList}}
