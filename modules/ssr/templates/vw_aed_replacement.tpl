{{*
 * @package Mediboard\Ssr
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=ssr script=planning}}
{{mb_script module=ssr script=planification}}

<script>
  Main.add(function() {
    Planification.showWeek(null, 'remplacement');
    Control.Tabs.create('tabs-replacement', true).activeLink.onmouseup();
  });
</script>

<table class="main">
  <tr style="height: 0.1%;">
    <td id="week-changer" colspan="2"></td>
  </tr>
  <tr style="height: 0.1%;">
    <td colspan="2">
      <ul id="tabs-replacement" class="control_tabs">
        <li>
          <a class="empty" href="#kines" onmouseup="ViewPort.SetAvlHeight.defer('sejours-kine', 1);">
            {{tr}}CReplacement-referents{{/tr}}
            <small>(-)</small>
          </a>
        </li>
        <li>
          <a class="empty" href="#reeducateurs" onmouseup="ViewPort.SetAvlHeight.defer('sejours-reeducateur', 1);">
            {{tr}}ssr-transfert_reeduc-{{$m}}{{/tr}}
            <small>(-)</small>
          </a>
        </li>

        <li style="float: right;">
          <button type="button" onclick="Planification.printRepartition();" class="print">
            {{tr}}ssr-repartition_patient{{/tr}}
          </button>
        </li>
      </ul>
    </td>
  </tr>
  <tr id="kines">  
    <td class="halfPane" style="vertical-align: top;">
      <div id="sejours-kine"></div>
    </td>
    <td id="replacement-kine"></td>
  </tr>
  <tr id="reeducateurs">
    <td class="halfPane" style="vertical-align: top;">
      <div id="sejours-reeducateur"></div>
    </td>
    <td id="replacement-reeducateur"></td>
  </tr>
</table>