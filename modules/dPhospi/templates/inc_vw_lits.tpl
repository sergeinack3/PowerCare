{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<button class="new" onclick="Infrastructure.addLit('{{$chambre->_id}}', '0','lits')">
  {{tr}}CLit-title-create{{/tr}}
</button>

<table id="lits" class="tbl">
  <tr>
    <th class="category" colspan="8">
      {{tr}}CChambre-back-lits{{/tr}} de la chambre {{mb_value object=$chambre field=nom}}
    </th>
  </tr>
  <tr>
    <th class="section">{{mb_label class=CLit field=rank}}</th>
    <th class="section">{{mb_label class=CLit field=nom}}</th>
    <th class="section">{{mb_label class=CLit field=nom_complet}}</th>
    <th class="section">{{mb_label class=CLit field=annule}}</th>
    {{if "atih"|module_active }}
      <th class="section">{{mb_label class=CLit field=identifie}}</th>
    {{/if}}
    <th class="section">{{tr}}CItemPrestation{{/tr}}</th>
    <th class="section"></th>
    <th class="section"></th>
  </tr>
  {{foreach from=$chambre->_ref_lits item=_lit}}
    {{mb_include module=dPhospi template=inc_vw_lit_line}}
    {{foreachelse}}
    <tr>
      <td class="empty" colspan="7">
        {{tr}}CLit-none{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>