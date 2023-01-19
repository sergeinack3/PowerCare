{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=hospi script=info_group ajax=true}}

<div id="infos_group">
  <button type="button" class="new" onclick="InfoGroup.editInfoGroup('0');" id="btn_new_info_group">
    {{tr}}CInfoGroup-title-create{{/tr}}
  </button>

  {{if 'dPhospi CInfoGroup split_by_users'|gconf}}
    <button type="button" class="fa fa-cog" onclick="InfoGroup.listInfoTypes();" id="btn_view_info_types">
      {{tr}}CInfoType-action-manage{{/tr}}
    </button>
    <script type="text/javascript">
      Main.add(function () {
        Control.Tabs.create('tab-info_types', true);
      });
    </script>
    <ul id="tab-info_types" class="control_tabs">
      {{foreach from=$list_infos_group item=data}}
        {{assign var=type value=$data.type}}
        {{assign var=infos value=$data.infos}}

        {{mb_ternary var=href test=$type->_id value=$type->_guid other='general'}}
        {{mb_ternary var=text test=$type->_id value=$type->name other='Général'}}

        {{assign var=count value=$infos|@count}}
        <li><a href="#{{$href}}"{{if !$count}} class="empty"{{/if}}>
            {{$text|smarty:nodefaults}}
            <small>({{$count}})</small>
          </a></li>
      {{/foreach}}
    </ul>
  {{foreach from=$list_infos_group item=data}}
    {{assign var=type value=$data.type}}
    {{assign var=infos value=$data.infos}}

    {{mb_ternary var=href test=$type->_id value=$type->_guid other='general'}}
    {{mb_ternary var=_inactive test=$type->_id value=$type->_count_inactive_infos other=$count_inactive}}
    <div id="{{$href}}">
      {{mb_include module=hospi template=inc_list_infos_group infos=$infos count_inactive=$_inactive}}
    </div>
  {{/foreach}}
  {{else}}

    {{mb_include module=hospi template=inc_list_infos_group infos=$list_infos_group}}
  {{/if}}
</div>
