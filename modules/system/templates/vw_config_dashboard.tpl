{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
Main.add(function() {
  Control.Tabs.create('config-dashboard-tabs');
});
</script>

<ul id="config-dashboard-tabs" class="control_tabs">
  {{foreach from=$modules key=_mod item=_infos}}
    <li><a href="#config-{{$_mod}}">{{$_mod}}</a></li>
  {{/foreach}}
</ul>

{{foreach from=$modules key=_mod item=_infos}}
  <div id="config-{{$_mod}}" style="display: none;">
    <ul>
      <li>Taille en SHM : {{$_infos.size}}</li>
      <li>Last update : {{$_infos.last_db_update}}</li>
      <li>Hash : {{$_infos.hash}}</li>
    </ul>

    <table class="main tbl">
      {{assign var=default_hash value=$_infos.contexts.global.0}}
      {{foreach from=$_infos.contexts key=_context item=_ids}}
        <tr>
          <th class="section" colspan="2">
            {{$_context}} ({{$_ids|@count}})
          </th>
        </tr>

        {{foreach from=$_ids key=_id item=_hash}}
          <tr>
            <td>{{if $_context != 'global'}}{{$_context}}-{{/if}}{{$_id}}</td>
            <td {{if $_hash == $default_hash}}class="warning"{{/if}}>{{$_hash}}</td>
          </tr>
        {{/foreach}}
      {{/foreach}}
    </table>
  </div>
{{/foreach}}


