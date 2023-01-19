{{*
 * @package Mediboard\Rpps
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function() {
    Control.Tabs.create('tabs-vw-synchro', true, {afterChange: function(container) {
      new Url('rpps', (container.id == 'tab-sync-external') ? 'vw_sync_external' : 'vw_sync_medecin').requestUpdate(container.id);
      }});
  });
</script>

<ul class="control_tabs" id="tabs-vw-synchro">
  <li><a href="#tab-sync-external">{{tr}}CExternalMedecinSync{{/tr}}</a></li>
  <li><a href="#tab-sync-medecin">{{tr}}CMedecin{{/tr}}</a></li>
</ul>

<div id="tab-sync-external" style="display: none"></div>
<div id="tab-sync-medecin" style="display: none"></div>