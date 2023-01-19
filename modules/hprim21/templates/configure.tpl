{{*
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-configure', true));
</script>

<ul id="tabs-configure" class="control_tabs">
  <li><a href="#config-treatment">{{tr}}config-hprim21-treatment{{/tr}}</a></li>
  <li><a href="#config-purge_echange">{{tr}}config-hprim21-purge_echange{{/tr}}</a></li>
  <li><a href="#config-source">{{tr}}config-hprim21-source{{/tr}}</a></li>
</ul>

<div id="config-treatment" style="display: none;">
  {{mb_include template=inc_config_treatment}}
</div>

<div id="config-purge_echange" style="display: none;">
  {{mb_include template=inc_config_purge_echange}}
</div>

<div id="config-source" style="display: none;">
  <h2>Paramètres par défaut du serveur ftp pour HPRIM 2.1</h2>

  <table class="form">  
    <tr>
      <th class="category">
        {{tr}}config-exchange-source{{/tr}}
      </th>
    </tr>
    <tr>
      <td> {{mb_include module=system template=inc_config_exchange_source source=$hprim21_source}} </td>
    </tr>
  </table>
</div>