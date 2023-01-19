{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  cutTools = function () {
    new Url("stock", "ajax_cut_units")
      .open();
  }
</script>

<table class="tbl">
  <tr>
    <td>
      {{if "bcb"|module_active && "dPmedicament"|module_active && 'Ox\Mediboard\Medicament\CMedicament::getBase'|static_call:null == "bcb"}}
        <button type="button" class="search" onclick="cutTools();">Découper les unités</button>
      {{/if}}
    </td>
  </tr>
</table>
