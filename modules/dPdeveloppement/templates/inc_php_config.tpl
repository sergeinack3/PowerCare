{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  toggleType = function(type, value) {
    $$('#php-config tr.'+type).invoke('setVisible', value);

    $$('#php-config-tabs a').each(function(a){
      var id = Url.parse(a.href).fragment,
          count = $(id).select("tr.edit-value").findAll(function(el) { return el.visible(); }).length;

      a.select('small')[0].update("("+count+")");
      a[count == 0 ? "addClassName" : "removeClassName"]("empty");
    });
  };

  Main.add(function() {
    Control.Tabs.create("php-config-tabs", true);

    toggleType('locked', $V($("show-locked")));
    toggleType('minor', $V($("show-minor")));
  });
</script>

<style type="text/css">
  tr.important th {
    font-weight: bold;
  }
</style>

<form name="editPHPConfig" method="post" onsubmit="return false">
  <table id="php-config">
    <tr>
      <td style="vertical-align: top;">
        <label><input type="checkbox" onclick="toggleType('minor', this.checked)" id="show-minor" /> Valeurs mineures</label>
        <br />
        <label><input type="checkbox" onclick="toggleType('locked', this.checked)" id="show-locked" /> Valeurs verrouillées</label>

        <ul class="control_tabs_vertical" id="php-config-tabs">
        {{foreach from=$php_config item=section key=name}}
          <li><a href="#php-{{$name}}" style="padding: 1px 4px;">{{$name}} <small></small></a></li>
        {{/foreach}}
        </ul>
      </td>
      <td style="vertical-align: top;">
        <table class="form">
          <tr>
            <th class="category"></th>
            <th class="category">global</th>
            <th class="category">local</th>
          </tr>
          {{foreach from=$php_config item=section key=name}}
            <tbody id="php-{{$name}}" style="display: none;">
            {{foreach from=$section item=value key=key}}
              {{assign var=access value=$value.user}}
              <tr class="edit-value {{if !$access}}locked{{/if}} {{if in_array($key, $php_config_important)}}important{{else}}minor{{/if}}">
                <th>{{$key}}</th>
                <td class="text">{{$value.global_value}}</td>
                <td class="text">{{$value.local_value}}</td>
              </tr>
            {{/foreach}}
            </tbody>
          {{/foreach}}
        </table>
      </td>
    </tr>
  </table>
</form>