{{*
 * @package Mediboard\Sip
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(Control.Tabs.create.curry('tabs-config-actions', true));
</script>

<table class="main">
  <tr>
    <td style="vertical-align: top;" class="narrow">
      <ul id="tabs-config-actions" class="control_tabs_vertical">
        <li>
          <a href="#actions-export">
            {{tr}}sip_config-actions-export{{/tr}}
          </a>
        </li>
       </ul>
    </td>
    <td style="vertical-align: top;">
      <div id="actions-export">
        {{mb_include template=inc_config_export}}
      </div>
    </td>
  </tr>
</table>