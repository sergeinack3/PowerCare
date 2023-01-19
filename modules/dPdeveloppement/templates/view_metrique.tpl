{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  function viewCurrent() {
    new Url("developpement", "view_metrique")
      .addParam("view_current", 1)
      .requestUpdate("current");
  }

  Main.add(Control.Tabs.create.curry("main_tab_group"));
</script>

<ul id="main_tab_group" class="control_tabs">
  <li><a href="#general">Général</a></li>
  {{if $nb_etabs > 1}}
    <li onmousedown="viewCurrent();"><a href="#current">{{$etab}}</a></li>
  {{/if}}
</ul>

<div id="general" style="display: none;">
  <table class="tbl main">
    <tr>
      <th>Type de données</th>
      <th>Quantité</th>
      <th>Dernière mise à jour</th>
    </tr>
    {{foreach from=$result item=_result key=class}}
    <tr>
      <td>{{tr}}{{$class}}{{/tr}}</td>
      <td>{{$_result.Rows|integer}}</td>
      <td>
        <label title="{{$_result.Update_time|date_format:$conf.datetime}}">
          {{$_result.Update_time|rel_datetime}}
        </label>
      </td>
    </tr>
    {{/foreach}}
  </table>
</div>

{{if $nb_etabs > 1}}
  <div id="current" style="display: none;"></div>
{{/if}}