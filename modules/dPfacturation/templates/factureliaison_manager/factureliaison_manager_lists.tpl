{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(
    function() {
      $('objects_list').fixedTableHeaders();
      $('factures_list').fixedTableHeaders();

    }
  );
</script>
<table class="main me-align-auto">
  <tbody class="viewported">
  <tr>
    <td class="viewport width50">
      <div id="objects_list" style="overflow: auto;">
        {{mb_include module=facturation template="factureliaison_manager/factureliaison_manager_objects_list"}}
      </div>
    </td>
    <td class="viewport width50">
      <div id="factures_list" style="overflow: auto;">
        {{mb_include module=facturation template="factureliaison_manager/factureliaison_manager_factures_list"}}
      </div>
    </td>
  </tr>
  </tbody>
</table>
