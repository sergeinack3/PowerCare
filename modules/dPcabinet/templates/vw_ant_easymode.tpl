{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<h2>
{{if $consult->_id}}
  {{$consult}}
{{elseif $patient->_id}}
  {{$patient}}
{{/if}}
</h2>

{{assign var=show_text_complet value=$app->user_prefs.show_text_complet}}
{{assign var=list_mode         value=$app->user_prefs.ant_trai_grid_list_mode}}

<script>
  toggleDisplay = function(className, status) {
    document.body.select("td." + className).each (function(elt) {
      if (status) {
        elt.show();
      }
      else {
        elt.hide();
      }
    });
    var antecedents;
    {{if $list_mode}}
      antecedents = $("antecedents").select("table.main");
    {{else}}
      // Refaire le count pour le volet Antécédents
      antecedents = $("antecedents").down("tr", 1).down("td", 1).select("table");
    {{/if}}

    antecedents.each(function(table) {
      var tab;
      {{if $list_mode}}
        tab = table.down("th.title");
      {{else}}
        tab = document.body.down("a[href=#"+table.id+"]");
      {{/if}}

      if (Object.isUndefined(tab)) {
        return;
      }

      var aides = table.select("td."+className);
      var small = tab.down("small");
      var count = parseInt(small.innerHTML.replace(/(\(|\))*/, ""));

      if (status) {
        count += aides.length;
      }
      else {
        count -= aides.length;
      }

      if (count < 0) {
        count = 0;
      }

      small.update("("+count+")");

      if (count == 0) {
        tab.addClassName("empty");
      }
      else {
        tab.removeClassName("empty");
      }

      // Ainsi que pour les sous-volets
      {{if $list_mode}}
        table.select("th.category").each(function(elt) {
          var nb_tds = elt.up("table").select("td.text").findAll(function(el) { return el.visible(); }).length;
          elt.down("small").update("("+nb_tds+")");
        });
      {{else}}
        table.select("a").each(function(elt) {
          var id = elt.href.split("#")[1];
          var tbody = $(id);

          var nb_tds = (tbody.select("td.text").findAll(function(el) { return el.visible(); })).length;
          var tab = document.body.down("a[href=#"+id+"]");

          if (Object.isUndefined(tab)) {
            return;
          }
          if (nb_tds == 0) {
            tab.addClassName("empty");
          }
          else {
            tab.removeClassName("empty");
          }
        });
      {{/if}}
    });
  };
  Main.add(function () {
    Control.Tabs.create('tab-main', false);

    {{if $app->user_prefs.check_establishment_grid_mode }}
      toggleDisplay('group', false);
    {{/if}}
  });
</script>

<ul id="tab-main" class="control_tabs">
  <li><a href="#antecedents">{{tr}}CAntecedent.more{{/tr}}</a></li>
  <li><a href="#traitements">{{tr}}CTraitement.more{{/tr}}</a></li>
  <li>
    <label class="me-margin-right-8">
      <input type="checkbox" checked onclick="toggleDisplay('user', this.checked)"> {{tr}}User{{/tr}}
    </label>
    <label class="me-margin-right-8">
      <input type="checkbox" checked onclick="toggleDisplay('function', this.checked)"> {{tr}}Function{{/tr}}
    </label>
    <label class="me-margin-right-8">
      <input type="checkbox" {{if !$app->user_prefs.check_establishment_grid_mode }}checked{{/if}} onclick="toggleDisplay('group', this.checked)">
      {{tr}}common-Establishment-court{{/tr}}
    </label>
  </li>
</ul>

{{mb_include template=inc_grid_antecedents}}
{{mb_include template=inc_grid_traitements}}

