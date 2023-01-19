{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  removeMovementDuplicates = function () {
    new Url("hospi", "do_remove_movement_duplicates", "dosql")
      .addFormData(getForm("remove-movement-duplicates-form"))
      .requestUpdate("remove-movement-duplicates", {method: "post"});
  }
</script>

<form name="remove-movement-duplicates-form" method="post" onsubmit="return false;">
  <table class="main tbl" style="table-layout: fixed;">
    <tr>
      <th class="title" colspan="2">Actions</th>
    </tr>
    <tr>
      <td>
        <button class="change" onclick="removeMovementDuplicates();">Supprimer les mouvements en doublon</button>

        <table class="main form">
          <tr>
            <th><label for="original_trigger_code">Code</label></th>
            <td><input type="text" name="original_trigger_code" value="A01" /></td>
          </tr>
          <tr>
            <th><label for="count">Nombre</label></th>
            <td><input type="text" name="count" value="10" /></td>
          </tr>
          <tr>
            <th><label for="do_it">Traiter</label></th>
            <td><input type="checkbox" name="do_it" value="1" /></td>
          </tr>
          <tr>
            <th><label for="auto">Automatique</label></th>
            <td><input type="checkbox" name="auto" value="1" /></td>
          </tr>
        </table>
      </td>
      <td id="remove-movement-duplicates"></td>
    </tr>
  </table>
</form>