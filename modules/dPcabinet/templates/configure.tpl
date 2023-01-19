{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module="cabinet" script="consultation"}}

<script>
  configCabinet = function() {
    new Url("cabinet", "ajax_config_cabinet").requestUpdate("cabinet_config");
  };

  Main.add(function() {
    var tabs = Control.Tabs.create('tabs-configure', true,
      { afterChange: function(container) {
          if (container.id == "CConfigEtab") {
            Configuration.edit('dPcabinet', ['CGroups', 'CFunctions CGroups.group_id'], $('CConfigEtab'));
          }
          else if (container.id == "cabinet_config") {
            configCabinet();
          }
        }
      }
    );
  });
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#CConfigEtab"    >{{tr}}CConfigEtab{{/tr}}</a></li>
  <li><a href="#actions"        >Autres actions             </a></li>
  <li><a href="#offline"        >Mode offline               </a></li>
  <li><a href="#cabinet_config" onmousedown="configCabinet();">{{tr}}cabinet-creator{{/tr}}</a></li>
</ul>


<div id="CConfigEtab" style="display: none"></div>

<div id="actions" style="display: none;">
 {{mb_include template=inc_configure_actions}}
</div>

<div id="offline" style="display: none;">
  <form method="get" name="genOffline" target="_blank">
    <table class="main tbl">
      <tr>
        <td class="narrow">Selectionnez un cabinet :
            <input type="hidden" name="m" value="{{$m}}">
            <input type="hidden" name="a" value="{{$a}}">
            <input type="hidden" name="_aio" value="1">
            <input type="hidden" name="dialog" value="1">
            <select name="function_id">
              {{foreach from=$functions_id item=_function}}
                <option value="{{$_function->_id}}">{{$_function}}</option>
              {{/foreach}}
            </select>
          </td>
        <td>
            <button class="button search" type="button" onclick="$V(this.form.a,'offline_programme_consult'); this.form.submit()">{{tr}}mod-dPcabinet-tab-offline_programme_consult{{/tr}}</button><br/>
            <button class="button search" type="button" onclick="$V(this.form.a,'vw_offline_consult_patients'); this.form.submit()">{{tr}}mod-dPcabinet-tab-vw_offline_consult_patients{{/tr}}</button>
        </td>
      </tr>
    </table>
  </form>
</div>

<div id="cabinet_config" style="display: none;"></div>
