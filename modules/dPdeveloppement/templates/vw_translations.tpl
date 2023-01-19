{{*
 * @package Mediboard\Developpement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
Main.add(function() {
  Control.Tabs.create('tabs-translations', true, {
    afterChange: function (container) {
      var file = (container.id === 'tab-vw-translations') ? 'displayTranslations' : 'vw_integrate_translations';

      var url = new Url();
      url.addParam('m', 'dPdeveloppement');
      url.addParam('a', file);
      url.requestUpdate(container.id);
    }
  });
});
</script>

<ul id="tabs-translations" class="control_tabs">
  <li><a href="#tab-vw-translations">{{tr}}mod-dPdeveloppement-tab-displayTranslations{{/tr}}</a></li>
  {{if $conf.debug}}
    <li><a href="#tab-vw-integrate-translations">{{tr}}mod-dPdeveloppement-tab-vw_integrate_translations{{/tr}}</a></li>
  {{/if}}
</ul>

<div id="tab-vw-translations" style="display: none;"></div>
{{if $conf.debug}}
  <div id="tab-vw-integrate-translations" style="display: none;"></div>
{{/if}}
