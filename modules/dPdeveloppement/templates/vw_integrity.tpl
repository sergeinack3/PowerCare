{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-integrity', true, {
      afterChange: function (container) {
        var url = new Url();
        url.addParam('m', 'dPdeveloppement');
        url.addParam('a', container.id);
        url.requestUpdate(container.id);
      }
    });
  });
</script>

<ul id="tabs-integrity" class="control_tabs">
  <li><a href="#vw_references">{{tr}}mod-dPdeveloppement-tab-vw_references{{/tr}}</a></li>
</ul>

<div id="vw_references" style="display: none;"></div>