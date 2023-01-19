{{*
 * @package Mediboard\Maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    Allaitement.refreshList('{{$patient_id}}', '{{$object_guid}}');
  });
</script>

<button class="new" onclick="Allaitement.editAllaitement(0, '{{$patient_id}}')"
        style="float: left;">{{tr}}CAllaitement-title-create{{/tr}}</button>

<table class="main layout">
  <tr>
    <td style="width: 40%">
      <div id="list_allaitements"></div>
      <div style="text-align: right;">
        <button type="button" class="cancel" onclick="Control.Modal.close();">{{tr}}Close{{/tr}}</button>
      </div>
    </td>
    <td id="edit_allaitement"></td>
  </tr>
</table>
