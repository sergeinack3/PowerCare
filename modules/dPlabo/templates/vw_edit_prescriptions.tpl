{{*
 * @package Mediboard\Labo
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=patients script=pat_selector}}

<script>
  var Catalogue = {
    select : function(iCatalogue) {
      if (isNaN(iCatalogue)) {
        iCatalogue = 0;
        var oForm = getForm("editCatalogue");
        if (oForm) {
          iCatalogue = $V(oForm.catalogue_labo_id);
        }
      }
      var urlCat = new Url("labo", "httpreq_vw_catalogues");
      var urlExam = new Url("labo", "httpreq_vw_examens_catalogues");

      urlCat.addNotNullParam("catalogue_labo_id", iCatalogue);
      urlExam.addNotNullParam("catalogue_labo_id", iCatalogue);

      urlCat.addParam("typeListe", $V(document.typeListeFrm.typeListe));
      urlCat.requestUpdate('topRightDiv');
      urlExam.requestUpdate('bottomRightDiv');
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
      var urlPack = new Url("dPlabo", "httpreq_vw_packs");
      var urlExam = new Url("dPlabo", "httpreq_vw_examens_packs");

      urlPack.addNotNullParam("pack_examens_labo_id", pack_id);
      urlExam.addNotNullParam("pack_examens_labo_id", pack_id);

      urlPack.addParam("dragPacks", 1);
      urlPack.addParam("typeListe", $V(document.typeListeFrm.typeListe));
      urlPack.requestUpdate("topRightDiv");
      urlExam.requestUpdate("bottomRightDiv");
    },
    dropExamenCat: function(sExamen_id, pack_id) {
      return false;
    },
    dropExamen: function(sExamen_id, pack_id) {
      return false;
    },
    delExamen: function(oForm) {
      var oFormBase = getForm("editPackItem");
      oFormBase.pack_examens_labo_id.value = oForm.pack_examens_labo_id.value;
      onSubmitFormAjax(oForm, Pack.select);
      return true;
    }
  };

  var Prescription = {
    select : function(prescription_id) {
      if (isNaN(prescription_id)) {
        prescription_id = 0;
        oForm = getForm("dropPrescriptionItem");
        if (oForm) {
          prescription_id = $V(oForm.prescription_labo_id);
        }
      }
      var iPatient_id = $V(getForm("patFrm").patient_id);
      var urlPresc = new Url("dPlabo", "httpreq_vw_prescriptions");
      var urlExam  = new Url("dPlabo", "httpreq_vw_examens_prescriptions");

      urlPresc.addNotNullParam("prescription_labo_id", prescription_id);
      urlExam.addNotNullParam("prescription_labo_id", prescription_id);

      urlPresc.addParam("patient_id", iPatient_id);
      urlPresc.requestUpdate("listPrescriptions");
      urlExam.requestUpdate("listExamens");
      Prescription.Examen.init(0);
    },

    edit : function(prescription_id) {
      var url = new Url("dPlabo", "httpreq_edit_prescription");
      url.addParam("prescription_labo_id", prescription_id);
      url.addParam("patient_id", getForm("patFrm").patient_id.value);
      url.requestUpdate("listExamens");
    },

    create : function() {
      var oPatientForm = getForm("patFrm");
      if(!oPatientForm.patient_id.value) {
        return false;
      }
      var oForm = getForm("editPrescription");
      oForm.praticien_id.value = {{$app->user_id}};
      oForm.patient_id.value = oPatientForm.patient_id.value;
      oForm.date.value = new Date().toDATETIME();
      onSubmitFormAjax(oForm, Prescription.select);
      return true;
    },

    results: function(prescription_id) {
      var url = new Url("labo", "vw_resultats", "tab");
      url.addParam("prescription_id", prescription_id);
      url.redirect();
    },

    del: function(oForm) {
      oForm.del.value = 1;
      onSubmitFormAjax(oForm, Prescription.select);
      return true;
    },

    lock: function(oForm) {
      oForm.verouillee.value = 1;
      onSubmitFormAjax(oForm, Prescription.select);
      return true;
    },

    valide: function(oForm) {
      oForm.validee.value = 1;
      onSubmitFormAjax(oForm, Prescription.select);
      return true;
    },

    print: function(prescription_id) {
      var url = new Url("labo", "vw_prescriptionPdf", "raw");
      url.addParam("prescription_id", prescription_id);
      url.popup(800, 700, "CPrescriptionLabo");
    },

    send: function(oForm) {
      $V(oForm.dosql, "do_prescription_export");
      onSubmitFormAjax(oForm, Prescription.select);
      return true;
    },

    Examen : {
      eSelected : null,

      init: function(iPrescriptionItem) {
        if($V(document.typeListeFrm.typeListe) == "Resultat") {
          Prescription.Examen.edit(iPrescriptionItem);
        }
        else {
          Prescription.Examen.select(iPrescriptionItem);
        }
      },

      select: function(iPrescriptionItem) {
        if (this.eSelected) {
          $(this.eSelected).removeClassName("selected");
        }

        this.eSelected = $("PrescriptionItem-"+iPrescriptionItem);

        if (this.eSelected) {
          this.eSelected.addClassName("selected");
        }
      },

      edit: function(iPrescriptionItem) {
        $V(document.typeListeFrm.typeListe, "Resultat", false);
        var urlResult = new Url("labo", "httpreq_edit_resultat");
        var urlGraph  = new Url("labo", "httpreq_graph_resultats");
        urlResult.addParam("typeListe", $V(document.typeListeFrm.typeListe));
        if (!isNaN(iPrescriptionItem)) {
          Prescription.Examen.select(iPrescriptionItem);
          urlResult.addParam("prescription_labo_examen_id", iPrescriptionItem);
          urlGraph.addParam("prescription_labo_examen_id", iPrescriptionItem);
        }
        urlResult.requestUpdate("topRightDiv");
        urlGraph.requestUpdate("bottomRightDiv");
      },

      del: function(oForm) {
        var oFormBase = getForm("dropPrescriptionItem");
        oFormBase.prescription_labo_id.value = oForm.prescription_labo_id.value;
        onSubmitFormAjax(oForm, Prescription.select);
        return true;
      },

      drop: function(sExamen_id, prescription_id) {
        var oFormBase = getForm("dropPrescriptionItem");
        aExamen_id = sExamen_id.split("-");
        if (aExamen_id[0] == "examenPack" || aExamen_id[0] == "examenCat") {
          oFormBase.dosql.value = "do_prescription_examen_aed";
          oFormBase.examen_labo_id.value = aExamen_id[1];
        }
        else if(aExamen_id[0] == "pack") {
          oFormBase.dosql.value = "do_prescription_pack_add";
          oFormBase._pack_examens_labo_id.value = aExamen_id[1];
        }
        oFormBase.prescription_labo_id.value = prescription_id;
        onSubmitFormAjax(oFormBase, Prescription.select);
        return true;
      }
    }
  };

  var Resultat = {
    select: function() {
      Prescription.Examen.edit();
    }
  };

  var oDragOptions = {
    revert: true,
    ghosting: true,
    starteffect : function(element) {
      $(element).addClassName("dragged");
      new Effect.Opacity(element, { duration:0.2, from:1.0, to:0.7 });
    },
    reverteffect: function(element, top_offset, left_offset) {
      var dur = Math.sqrt(Math.abs(top_offset^2)+Math.abs(left_offset^2))*0.02;
      element._revert = new Effect.Move(element, {
        x: -left_offset,
        y: -top_offset,
        duration: dur,
        afterFinish : function (effect) {
          $(effect.element).removeClassName("dragged");
        }
      } );
    },
    endeffect: function(element) {
      new Effect.Opacity(element, { duration:0.2, from:0.7, to:1.0 } );
    }
  };

  // Recherche des analyses
  function search() {
    new Url("labo", "httpreq_search_exam")
      .addParam("recherche", getForm("frmRecherche").search.value)
      .requestUpdate("bottomRightDiv");
  }

  Main.add(function() {
    ViewPort.SetAvlHeight('topRightDiv'      , 0.4);
    ViewPort.SetAvlHeight('bottomRightDiv'   , 1);
    ViewPort.SetAvlHeight('listPrescriptions', 0.4);
    ViewPort.SetAvlHeight('listExamens'      , 1);
    Prescription.select();
    window[$V(document.typeListeFrm.typeListe)].select();

    PatSelector.sForm = "patFrm";
    PatSelector.sId   = "patient_id";
    PatSelector.sView = "patNom";

    // Debugage du scroll de la div de la liste des prescriptions
    Position.includeScrollOffsets = true;
    Event.observe('listPrescriptions', 'scroll', function(event) { Position.prepare(); });

    // Pour éviter de dropper en dessous du tableau de la liste des analyses
    Droppables.add('viewport-listExamens', oDragOptions );
  });
</script>

<table class="main">
  <tr>
    <td class="halfPane">
      <form name="patFrm" action="?" method="get">
      <table class="form">
        <tr>
          <th>
            <label for="patNom" title="Merci de choisir un patient pour voir son dossier">Choix du patient</label>
          </th>
          <td>
            <input type="hidden" name="m" value="dPlabo" />
            <input type="hidden" name="patient_id" value="{{$patient->_id}}" onchange="this.form.submit()"/>
            <input type="hidden" name="prescription_labo_id" value="" />
            <span onmouseover="ObjectTooltip.createEx(this,'{{$patient->_guid}}')">
            <input type="text" readonly="readonly" name="patNom" value="{{$patient->_view}}" />
            </span>
            <button class="search me-primary" type="button" onclick="PatSelector.pop();">Chercher</button>
            {{if $patient->_id}}
            <button class="new" type="button" onclick="Prescription.edit();">
              Prescrire
            </button>
            <button class="cancel notext" type="button" onclick="PatSelector.set(0, '&mdash;');">
              Cancel
            </button>
            {{/if}}
          </td>
        </tr>
      </table>
      </form>
      <form name="editPrescription" action="?m={{$m}}" method="post" onsubmit="return checkForm(this)">
        <input type="hidden" name="m" value="dPlabo" />
        <input type="hidden" name="dosql" value="do_prescription_aed" />
        <input type="hidden" name="prescription_labo_id" value="" />
        <input type="hidden" name="praticien_id" value="" />
        <input type="hidden" name="patient_id" value="" />
        <input type="hidden" name="date" value="" />
        <input type="hidden" name="del" value="0" />
      </form>
    </td>
    <td class="halfPane">
      <form name="typeListeFrm" action="?" method="get">
      <input type="hidden" name="m" value="dPlabo" />
      <table class="form">
        <tr>
          <td class="button">
            <input type="radio" name="typeListe" value="Pack" {{if $typeListe == "Pack" || $typeListe == ""}}checked{{/if}} onchange="window[this.value].select();" />
            <label for="typeListe_Pack">Packs</label>
            <input type="radio" name="typeListe" value="Catalogue" {{if $typeListe == "Catalogue"}}checked{{/if}} onchange="window[this.value].select();" />
            <label for="typeListe_Catalogue">Catalogues</label>
            <input type="radio" name="typeListe" value="Resultat" {{if $typeListe == "Resultat"}}checked{{/if}} onchange="window[this.value].select();" />
            <label for="typeListe_Resultat">Saisie résultats</label>
          </td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  <tbody class="viewported">
    <tr>
      <td id="viewport-listPrescriptions" class="viewport">
        <div id="listPrescriptions"></div>
      </td>
      <td class="viewport">
        <div id="topRightDiv"></div>
      </td>
    </tr>
    <tr>
      <td id="viewport-listExamens" class="viewport">
        <div id="listExamens"></div>
      </td>
      <td class="viewport">
        <div id="bottomRightDiv"></div>
      </td>
    </tr>
  </tbody>
</table>