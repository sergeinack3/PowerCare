{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  var Catalogue = {
    select : function(iCatalogue) {
      if (isNaN(iCatalogue)) {
        iCatalogue = 0;
        var form = getForm("editCatalogue");
        if (form) {
          iCatalogue = $V(form.catalogue_labo_id.value);
        }
      }
      var urlCat  = new Url("labo", "httpreq_vw_catalogues");
      var urlExam = new Url("labo", "httpreq_vw_examens_catalogues");

      urlCat.addNotNullParam("catalogue_labo_id", iCatalogue);
      urlExam.addNotNullParam("catalogue_labo_id", iCatalogue);

      urlCat.requestUpdate('CataloguesView');
      urlExam.requestUpdate('CataloguesExamensView');
    }
  };

  var Pack = {
    select : function(pack_id) {
      if (isNaN(pack_id)) {
        pack_id = 0;
        var oForm = getForm("editPackItem");
        if (oForm) {
          pack_id = $V(oForm.pack_examens_labo_id);
        }
      }
      var urlPack = new Url("labo", "httpreq_vw_packs");
      var urlExam = new Url("labo", "httpreq_vw_examens_packs");

      urlPack.addNotNullParam("pack_examens_labo_id", pack_id);
      urlExam.addNotNullParam("pack_examens_labo_id", pack_id);

      urlPack.requestUpdate('PacksView');
      urlExam.requestUpdate('PacksExamensView');
    },
    dropExamenCat: function(sExamen_id, pack_id) {
      var oFormBase = getForm("editPackItem");
      var aExamen_id = sExamen_id.split("-");
      if (aExamen_id[0] == "examenCat") {
        oFormBase.examen_labo_id.value       = aExamen_id[1];
        oFormBase.pack_examens_labo_id.value = pack_id;
        onSubmitFormAjax(oFormBase, Pack.select);
        return true;
      }
      else {
        return false;
      }
    },
    dropExamen: function(sExamen_id, pack_id) {
      var oFormBase = document.editPackItem;
      var aExamen_id = sExamen_id.split("-");
      if (aExamen_id[0] == "examenPack" || aExamen_id[0] == "examenCat") {
        oFormBase.examen_labo_id.value       = aExamen_id[1];
        oFormBase.pack_examens_labo_id.value = pack_id;
        onSubmitFormAjax(oFormBase, Pack.select);
        return true;
      }
      else {
        return false;
      }
    },
    delExamen: function(oForm) {
      var oFormBase = getForm("editPackItem");
      oFormBase.pack_examens_labo_id.value = oForm.pack_examens_labo_id.value;
      onSubmitFormAjax(oForm, Pack.select);
      return true;
    }
  };

  var oDragOptions = {
    revert: true,
    ghosting: true,
    starteffect : function(element) {
      Element.classNames(element).add("dragged");
      new Effect.Opacity(element, { duration:0.2, from:1.0, to:0.7 });
    },
    reverteffect: function(element, top_offset, left_offset) {
      var dur = Math.sqrt(Math.abs(top_offset^2)+Math.abs(left_offset^2))*0.02;
      element._revert = new Effect.Move(element, {
        x: -left_offset,
        y: -top_offset,
        duration: dur,
        afterFinish : function (effect) {
          Element.classNames(effect.element.id).remove("dragged");
        }
      } );
    },
    endeffect: function(element) {
      new Effect.Opacity(element, { duration:0.2, from:0.7, to:1.0 } );
    }
  };

  Main.add(function() {
    Pack.select();
    Catalogue.select();
    ViewPort.SetAvlHeight('PacksView'            , 0.4);
    ViewPort.SetAvlHeight('PacksExamensView'     , 1);
    ViewPort.SetAvlHeight('CataloguesView'       , 0.4);
    ViewPort.SetAvlHeight('CataloguesExamensView', 1);

    // Debugage du scroll de la div de la liste des prescriptions
    Position.includeScrollOffsets = true;
    Event.observe('PacksView', 'scroll', function(event) { Position.prepare(); });

    // Pour éviter de dropper en dessous du tableau de la liste des analyses
    Droppables.add('viewport-PacksExamensView', oDragOptions );
  });

  // Recherche des analyses
  function search() {
    new Url("dPlabo", "httpreq_search_exam")
      .addParam("recherche", $V(getForm("frmRecherche").search))
      .requestUpdate("CataloguesExamensView");
  }
</script>

<table class="main">
  <tr>
    <th class="halfPane">
      Packs
    </th>
    <th class="halfPane">
      Catalogues
    </th>
  </tr>
  <tbody class="viewported">
  <tr>
    <td class="viewport" id="viewport-PacksView">
      <div id="PacksView"></div>
    </td>
    <td class="viewport">
      <div id="CataloguesView"></div>
    </td>
  </tr>
  <tr>
    <td class="viewport" id="viewport-PacksExamensView">
      <div id="PacksExamensView"></div>
    </td>
    <td class="viewport">
      <div id="CataloguesExamensView"></div>
    </td>
  </tr>
  </tbody>
</table>