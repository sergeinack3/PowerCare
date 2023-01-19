{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cabinet script=tarif ajax=1}}

<script>
  Tarif.refreshTarifs = function() {
    Tarif.reloadListTarifs('{{$prat->_id}}', 'CMediusers');
    Tarif.reloadListTarifs('{{$prat->_id}}', 'CFunctions');
    {{if "dPcabinet Tarifs show_tarifs_etab"|gconf}}
      Tarif.reloadListTarifs('{{$prat->_id}}', 'CGroups');
    {{/if}}
  }

  Main.add(function() {
    Tarif.refreshTarifs();
  });
</script>

<table class="main">
  <tr>
    <td colspan="10">
      <form action="?" name="selectPrat" method="get">
        <input type="hidden" name="tarif_id" value="" />
        <input type="hidden" name="m" value="{{$m}}" />
        <select name="prat_id"
                onchange="Tarif.selectPrat(this.value, {{if "dPcabinet Tarifs show_tarifs_etab"|gconf}}1{{else}}0{{/if}})">
          <option value="">&mdash; Aucun praticien</option>
          {{mb_include module=mediusers template=inc_options_mediuser selected=$prat->_id list=$listPrat}}
        </select>
      </form>
    </td>
  </tr>
  <tr>
    <td class="halfPane" style="width: 33%;" id="tarifs_CMediusers">
    </td>

    <td class="halfPane" style="width: 33%;" id="tarifs_CFunctions">
    </td>

    {{if "dPcabinet Tarifs show_tarifs_etab"|gconf}}
      <td class="halfPane" id="tarifs_CGroups">
      </td>
    {{/if}}
  </tr>
</table>