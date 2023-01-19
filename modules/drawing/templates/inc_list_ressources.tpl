{{*
 * @package Mediboard\Drawing
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs_ressources', true);
  });

  reloadAfterUploadFile = refreshList;
</script>

<ul class="control_tabs" id="tabs_ressources">
  {{if $user->_id}}
    <li>
      <a href="#user-tab" {{if !$user->_ref_drawing_cat|@count}}class="empty"{{/if}}>
        <small class="me-no-display">{{tr}}CMediusers{{/tr}}</small><br/>
        {{$user}}
      </a>
    </li>
  {{/if}}
  {{foreach from=$functions item=_function}}
    <li>
      <a href="#function_{{$_function->_id}}-tab" {{if !$_function->_ref_drawing_cat|@count}}class="empty"{{/if}}>
        <small class="me-no-display">{{tr}}CFunctions{{/tr}}</small><br/>
        {{$_function}}
      </a>
    </li>
  {{/foreach}}
  <li>
    <a href="#group-tab" {{if !$group->_ref_drawing_cat|@count}}class="empty"{{/if}}>
      <small class="me-no-display">{{tr}}CGroups{{/tr}}</small><br/>
      {{$group}}
    </a>
  </li>
</ul>

{{if $user->_id}}
  <div id="user-tab" style="display: none">
    <button class="new" onclick="DrawingCategory.editModal('', 'user', '{{$user->_id}}', refreshList)">{{tr}}CDrawingCategory-title-create{{/tr}}</button>
    {{foreach from=$user->_ref_drawing_cat item=_cat}}
      {{mb_include template=inc_list_cat object=$_cat}}
    {{/foreach}}
  </div>
{{/if}}
{{foreach from=$functions item=_function}}
  <div id="function_{{$_function->_id}}-tab" style="display: none">
    <button class="new" onclick="DrawingCategory.editModal('', 'function', '{{$_function->_id}}', refreshList)">{{tr}}CDrawingCategory-title-create{{/tr}}</button>
    {{foreach from=$_function->_ref_drawing_cat item=_cat}}
      {{mb_include template=inc_list_cat object=$_cat}}
    {{/foreach}}
  </div>
{{/foreach}}
<div id="group-tab" style="display: none">
  <button class="new" onclick="DrawingCategory.editModal('', 'group', '{{$group->_id}}', refreshList)">{{tr}}CDrawingCategory-title-create{{/tr}}</button>
  {{foreach from=$group->_ref_drawing_cat item=_cat}}
    {{mb_include template=inc_list_cat object=$_cat}}
  {{/foreach}}
</div>
