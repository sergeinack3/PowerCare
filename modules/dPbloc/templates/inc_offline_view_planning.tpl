{{*
 * @package Mediboard\Bloc
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$offline}}
  {{mb_return}}
{{/if}}


<style>
  @media print {
    div.modal_view {
      display: block !important;
      height: auto !important;
      width: 100% !important;
      font-size: 8pt !important;
      left: auto !important;
      top: auto !important;
      position: static !important;
    }
    table.table_print {
      page-break-after: always;
    }
    table {
      width: 100% !important;
      font-size: inherit !important;
    }
  }

  table.table_print {
    page-break-after: always;
  }
</style>

<script>
  // La div du dossier qui a été passé dans la fonction Modal.open()
  // a du style supplémentaire, qu'il faut écraser lors de l'impression
  // d'un dossier seul.
  printOneDossier = function(sejour_id) {
    Element.print($("dossier-"+sejour_id).childElements());
  };

  printFiches = function() {
    var fiches_anesth = $("fiches_anesth");
    var sejours = $$(".dossier_sejour");

    // On empêche l'impression des dossiers de soins
    sejours.invoke("removeClassName", "modal_view");
    sejours.invoke("addClassName", "not-printable");
    fiches_anesth.update();

    // On clone les fiches
    $$("div.fiche_anesth").each(function(fiche_anesth) {
      var clone_fiche = fiche_anesth.cloneNode(true);
      clone_fiche.show();
      clone_fiche.setStyle({"page-break-after": "always"});
      fiches_anesth.insert(clone_fiche);
    });

    // Retrait du saut de page pour la dernière fiche
    fiches_anesth.lastChild.setStyle({"page-break-after": "auto"});

    window.print();

    // On réactive les dossiers de soins pour l'impression
    sejours.invoke("addClassName", "modal_view");
    sejours.invoke("removeClassName", "not-printable");
    fiches_anesth.update();
  };
</script>

{{foreach from=$dossiers_soins item=_dossier_soin key=sejour_id name=dossier}}
  <div id="dossier-{{$sejour_id}}" style="display: none; {{if !$smarty.foreach.dossier.last}}page-break-after: always;{{/if}}" class="modal_view dossier_sejour">
    {{$_dossier_soin|smarty:nodefaults}}
  </div>
{{/foreach}}

<div id="fiches_anesth" class="only-printable"></div>
