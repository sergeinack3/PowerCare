{{*
 * @package Mediboard\Files
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-groups');
  });
</script>

<div>
  <span onmouseover="ObjectTooltip.createEx(this, '{{$cat->_guid}}')">{{tr}}CFilesCategory{{/tr}} : {{$cat->nom}}</span>
</div>

<ul id="tabs-groups" class="control_tabs me-control-tabs-wraped">
  {{foreach from=$stats item=stat}}
    <li><a href="#tab-{{$stat.object->_id}}">{{$stat.object->text}} ({{$stat.count}})</a></li>
  {{/foreach}}
</ul>

{{foreach from=$stats key=id item=stat}}
  <div id="tab-{{$stat.object->_id}}" style="display: none;">

    <div>
      <span onmouseover="ObjectTooltip.createEx(this, '{{$stat.object->_guid}}')">{{$stat.object}}</span>
    </div>

    <table class="main tbl">
      <tr>
        <th>{{tr}}CFile-author_id{{/tr}}</th>
        <th>{{tr}}CFilesCategoryDispatcher-Title-Count{{/tr}}</th>
      </tr>

      {{foreach from=$stat.users item=user}}
        <tr>
          <td>{{$user.object}}</td>
          <td class="narrow">{{$user.count|number_format:'0':',':' '}}</td>
        </tr>
      {{/foreach}}

      <tr>
        <td colspan="2" class="button">
          <form name="dispatch-file-cat-{{$id}}" method="post" onsubmit="return onSubmitFormAjax(this, {onComplete: function() {Control.Modal.refresh();}})">
            <input type="hidden" name="m" value="files"/>
            <input type="hidden" name="dosql" value="do_dispatch_files_cat"/>
            <input type="hidden" name="cat_id" value="{{$cat->_id}}"/>
            <input type="hidden" name="group_id" value="{{$id}}"/>

            <button type="submit" class="change">{{tr}}CFilesCategory-Action-Dispatch for group{{/tr}}</button>
          </form>
        </td>
      </tr>
    </table>
  </div>
  {{foreachelse}}
  <div class="empty">
    {{tr}}CFilesCategory-back-categorized_files.empty{{/tr}}
  </div>
{{/foreach}}
