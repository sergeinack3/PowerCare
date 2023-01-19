{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=autocomplete}}
{{mb_script module=stock script=product_stock}}

<script>
  Main.add(function () {
    var tabs = Control.Tabs.create('tab_setup', true);
    refreshTab(tabs.activeContainer.id, tabs.activeLink.up('li'));
  });

  function refreshTab(tab_name, element) {
    var url = new Url("stock", tab_name);
    url.requestUpdate(tab_name);
    if (element) {
      element.onmousedown = null;
    }
  }
</script>

<!-- Tabs titles -->
<ul id="tab_setup" class="control_tabs">
  {{foreach from=$tabs item=_tab}}
  <li onmousedown="refreshTab('{{$_tab}}', this)">
    <a href="#{{$_tab}}">{{tr}}mod-dPstock-tab-{{$_tab}}{{/tr}}</a>
  </li>
  {{/foreach}}
</ul>

<!-- Tabs containers -->
{{foreach from=$tabs item=_tab}}
<div id="{{$_tab}}" class="me-margin-top-8" style="display: none;"></div>
{{/foreach}}
