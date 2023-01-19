{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  CorrespondantModele = {
    editCorrespondant: function (correspondant_id) {
      var url = new Url("patients", "ajax_edit_correspondant_modele");
      url.addParam("correspondant_id", correspondant_id);
      url.requestModal(600, 400);
    },

    refreshList: function (correspondant_id) {
      var url = new Url("patients", "ajax_list_correspondants_modele");
      if (correspondant_id) {
        url.addParam("correspondant_id", correspondant_id);
      }
      url.requestUpdate("list_correspondants");
    },

    afterSave: function (correspondant_id) {
      Control.Modal.close();
      CorrespondantModele.refreshList(correspondant_id ? correspondant_id : null);
    },

    updateSelected: function (elt) {
      $("list_correspondants").select("tr").invoke("removeClassName", "selected");
      if (elt) {
        elt.addClassName("selected");
      }
    }
  };

  Main.add(function () {
    CorrespondantModele.refreshList();
  });

  function popupImport() {
    var url = new Url('patients', 'assurance_import_csv');
    url.popup(800, 600, 'Import des assurances');
  }
</script>

<button type="button" class="new" onclick="CorrespondantModele.editCorrespondant(0)">
  {{tr}}CCorrespondant-title-create{{/tr}}
</button>

<button type="button" class="upload" onclick="popupImport();" class="hslip">{{tr}}Import-assurance{{/tr}}</button>

<table class="tbl" id="list_correspondants">
</table>
