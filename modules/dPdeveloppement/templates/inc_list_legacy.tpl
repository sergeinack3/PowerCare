{{*
* @package Mediboard\Developpement
* @author  SAS OpenXtrem <dev@openxtrem.com>
* @license https://www.gnu.org/licenses/gpl.html GNU General Public License
* @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<table class="main">
  <tr>
    <td class="narrow">
      <ul id="tab-legacy" class="control_tabs_vertical small" style="width:20em;">
        <li>
          <a href="#legacy-actions" style="line-height: 24px;">
            LegacyController
            <small>({{$count_actions}})</small>
          </a>
        </li>
        <li>
          <a href="#legacy-scripts" style="line-height: 24px;">
            scripts.php
            <small>({{$count_scripts}})</small>
          </a>
        </li>
        <li>
          <a href="#legacy-dosql" style="line-height: 24px;">
            controllers/dosql.php
            <small>({{$count_dosql}})</small>
          </a>
        </li>
      </ul>
      <script>
        Main.add(Control.Tabs.create.curry('tab-legacy', true));
      </script>
    </td>
    <td>
      <table class="tbl">
        <tbody style="display: none;" id="legacy-actions">
        <tr>
          <th>Module</th>
          <th>LegacyController</th>
          <th>Action</th>
        </tr>
        {{foreach from=$actions item=_datas key=_module}}
          {{foreach from=$_datas item=_controller key=_action}}
            <tr>
              <td>{{mb_ditto name=module value=$_module}}</td>
              <td>{{mb_ditto name=controller value=$_controller}}</td>
              <td>{{$_action}}</td>
            </tr>
          {{/foreach}}
        {{/foreach}}
        </tbody>
        <tbody style="display: none;" id="legacy-scripts">
        <tr>
          <th>Module</th>
          <th>script.php</th>
        </tr>
        {{foreach from=$scripts item=_datas key=_module}}
          {{foreach from=$_datas item=_script}}
            <tr>
              <td>{{mb_ditto name=module value=$_module}}</td>
              <td>{{$_script}}</td>
            </tr>
          {{/foreach}}
        {{/foreach}}
        </tbody>
        <tbody style="display: none;" id="legacy-dosql">
        <tr>
          <th>Module</th>
          <th>controllers/dosql.php</th>
        </tr>
        {{foreach from=$dosql_scripts item=_datas key=_module}}
          {{foreach from=$_datas item=_script}}
            <tr>
              <td>{{mb_ditto name=module value=$_module}}</td>
              <td>{{$_script}}</td>
            </tr>
          {{/foreach}}
        {{/foreach}}
        </tbody>
      </table>
    </td>
  </tr>
</table>
