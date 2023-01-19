{{*
 * @package Mediboard\Hospi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=dPhospi script=prestation ajax=1}}

<script>
  Main.add(function () {
    Prestation.editPrestation('{{$prestation_id}}', '{{$object_class}}');
    Prestation.refreshList('{{$object_class}}-{{$prestation_id}}');
  });
</script>

{{* Formulaire pour la suppression de sous-items *}}
<form name="delSousItemForm" method="post">
  <input type="hidden" name="m" value="hospi" />
  {{mb_class class=CSousItemPrestation}}
  <input type="hidden" name="del" value="1" />
  <input type="hidden" name="sous_item_prestation_id" />
</form>

{{mb_include template=inc_warning_config_prestations wanted=expert}}

<div class="me-margin-top-4">
  {{* Formulaire fictif pour récupérer le type de prestation *}}
  <form name="new_prestation" method="get">
    <button type="button" class="new me-primary"
            onclick="Prestation.removeSelected('prestation'); Prestation.editPrestation(0, $V(this.form.type_prestation))">
      Création de prestation
    </button>
    <label>
      <input type="radio" name="type_prestation" id="type_prestation" value="CPrestationPonctuelle" checked /> Ponctuelle
    </label>
    <label>
      <input type="radio" name="type_prestation" id="type_prestation" value="CPrestationJournaliere" /> Journalière
    </label>
    <button type="button" class="import" onclick="Prestation.importPrestation();">{{tr}}CPrestationExpert-import{{/tr}}</button>
    <button type="button" class="fas fa-external-link-alt"
            onclick="Prestation.exportPrestation();">{{tr}}CPrestationExpert-export{{/tr}}</button>

  </form>
</div>

<table class="main">
  <tr>
    <td id="list_prestations" style="width: 50%;"></td>
    <td id="edit_prestation"></td>
  </tr>
</table>
