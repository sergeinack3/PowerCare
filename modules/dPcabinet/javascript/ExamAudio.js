/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ExamAudio = {
  /**
   * Raffraichissement des graphs
   *
   * @param type
   * @param side
   * @param old_exam_audio_id
   */
  updateGraph: function (type, side, old_exam_audio_id) {
    old_exam_audio_id = (old_exam_audio_id) ? old_exam_audio_id : null;
    var oForm = getForm("editFrm");
    
    var url = new Url("cabinet", "ajax_exam_audio_graph", "raw");
    url.addParam("examaudio_id", oForm.examaudio_id.value);
    url.addParam("type", type);
    url.addParam("side", side);
    url.addParam("old_exam_audio_id", old_exam_audio_id);

    url.requestJSON(function (data) {
      if (!data) {
        return;
      }
      
      var draw = function(data) {
        var plot = $(data.id).retrieve("plot");

        plot.setData(data.series);
        plot.setupGrid();
        plot.draw();
      };
      
      if (data.id) {
        draw(data);
      }
      else {
        data.each(draw);
      }
    });
  },

  /**
   * Mise à jour du tableau bilan
   *
   * @param print
   * @param old_exam_audio_id
   */
  updateBilan: function (print, old_exam_audio_id) {
    old_exam_audio_id = (old_exam_audio_id) ? old_exam_audio_id : null;
    var oForm = getForm("editFrm");
    var url = new Url("dPcabinet", "httpreq_vw_examaudio_bilan");
    url.addParam("examaudio_id", oForm.examaudio_id.value);
    url.addParam("old_exam_audio_id", old_exam_audio_id);
    if (print) {
      url.requestUpdate('td_bilan', function () {
        ExamAudio.printExamAudio();
      });
    } else {
      url.requestUpdate('td_bilan');
    }
  },

  /**
   * Mise à jour de tous les tableaux et graphs
   *
   * @param print
   * @param old_exam_audio_id
   */
  updateAll: function (print, old_exam_audio_id) {
    old_exam_audio_id = (old_exam_audio_id) ? old_exam_audio_id : null;
    ExamAudio.updateGraph("all", null, old_exam_audio_id);
    ExamAudio.updateBilan(print, old_exam_audio_id);
  },

  /**
   * Rafraichissement des graphs d'audiométrie tonale
   *
   * @param old_exam_audio_id
   */
  updateAudiometrieTonale: function (old_exam_audio_id) {
    old_exam_audio_id = (old_exam_audio_id) ? old_exam_audio_id : null;
    ExamAudio.updateGraph("audiometrie_tonale", "gauche", old_exam_audio_id);
    ExamAudio.updateGraph("audiometrie_tonale", "droite", old_exam_audio_id);
  },

  /**
   * Rafraichissement des graphs tympanométrie
   *
   * @param old_exam_audio_id
   */
  updateTympanometrie: function (old_exam_audio_id) {
    old_exam_audio_id = (old_exam_audio_id) ? old_exam_audio_id : null;
    ExamAudio.updateGraph("tympanometrie", "gauche", old_exam_audio_id);
    ExamAudio.updateGraph("tympanometrie", "droite", old_exam_audio_id);
  },

  // Audiometrie tonale
  tonalPerteMin: -10,
  tonalPerteMax: 120,
  indexFrequenceMax: 7,

  /**
   * Changement des valeurs en fonction d'où clique l'utilisateur sur les graphs d'audiométrie tonale
   *
   * @param sCote
   * @param sConduction
   * @param iFrequence
   * @param iNewValue
   * @param old_exam_audio_id
   */
  changeTonalValue: function (sCote, sConduction, iFrequence, iNewValue, old_exam_audio_id) {
    old_exam_audio_id = (old_exam_audio_id) ? old_exam_audio_id : null;
    var oForm = getForm("editFrm");

    if (!sConduction) {
      sConduction = $V(oForm._conduction);
    }

    var sElementName = printf("_%s_%s[%i]", sCote, sConduction, iFrequence);
    var oElement = oForm.elements[sElementName];
    var nFrequence = 125 * Math.pow(2, iFrequence);

    // Do not use !iNewValue which is also true for a 0 value    
    if (iNewValue == null) {
      var sInvite = printf("Modifier la perte pour l'oreille %s à %iHz'", sCote, nFrequence);
      var sAdvice = printf("Merci de fournir une valeur comprise (en dB) entre %i et %i", ExamAudio.tonalPerteMin, ExamAudio.tonalPerteMax);

      iNewValue = prompt(sInvite + "\n" + sAdvice, oElement.value);

      // Do not user !iNewValue which is also true for empty string    
      if (iNewValue == null) {
        return;
      }

      if (isNaN(iNewValue) || iNewValue < ExamAudio.tonalPerteMin || iNewValue > ExamAudio.tonalPerteMax) {
        alert("Valeur incorrecte : " + iNewValue + "\n" + sAdvice);
        return;
      }
    }

    oElement.value = parseInt(iNewValue);
    return onSubmitFormAjax(oForm, {
      onComplete: ExamAudio.updateGraph.curry("audiometrie_tonale", sCote, old_exam_audio_id)
    });
  },

  // Audiometrie vocale
  minVocalDB: 0,
  maxVocalDB: 120,
  minVocalPc: 0,
  maxVocalPc: 100,
  maxKey: 7,

  /**
   * Changement des valeurs en fonction d'où clique l'utilisateur sur le graph d'audiométrie tonale
   *
   * @param sCote
   * @param iSelectedDB
   * @param iNewDBValue
   * @param iNewPcValue
   * @param old_exam_audio_id
   */
  changeVocalValue: function (sCote, iSelectedDB, iNewDBValue, iNewPcValue, old_exam_audio_id) {
    old_exam_audio_id = (old_exam_audio_id) ? old_exam_audio_id : null;
    var oForm = getForm("editFrm");
    sCote = sCote || $V(oForm._oreille);

    for (var iKey = 0; iKey <= ExamAudio.maxKey; ++iKey) {
      var sElements = printf("_%s_vocale[%i]", sCote, iKey);
      var oElementDB = oForm.elements[sElements + "[0]"];
      var oElementPc = oForm.elements[sElements + "[1]"];

      if (!oElementDB.value && !oElementPc.value) {
        break;
      }

      if (oElementDB.value == iSelectedDB) {
        break;
      }
    }

    if (iKey > ExamAudio.maxKey && iSelectedDB) {
      alert("Impossible d'ajouter un point supplémentaire");
      return;
    }

    var sElements = printf("_%s_vocale[%i]", sCote, iKey);

    var oElementDB = oForm.elements[sElements + "[0]"];

    if (!iNewDBValue) {
      var sInvite = printf("Modifier la valeur de réponse pour le point #%d concernant l'oreille %s", iKey, sCote);
      var sAdvice = printf("Merci de fournir une valeur comprise (en dB) entre %i et %i", ExamAudio.minVocalDB, ExamAudio.maxVocalDB);

      iNewDBValue = prompt(sInvite + "\n" + sAdvice, oElementDB.value);

      // Do not user !iNewValue which is also true for empty string    
      if (iNewDBValue == null) {
        return;
      }

      if (isNaN(iNewDBValue) || iNewDBValue < ExamAudio.minVocalDB || iNewDBValue > ExamAudio.maxVocalDB) {
        alert("Valeur incorrecte : " + iNewDBValue + "\n" + sAdvice);
        return;
      }
    }

    var oElementPc = oForm.elements[sElements + "[1]"];
    if (!iNewPcValue) {
      sInvite = printf("Modifier le pourcentage pour le point #%d concernant l'oreille %s", iKey, sCote);
      sAdvice = printf("Merci de fournir une valeur comprise (en pourcentage) entre %i et %i", ExamAudio.minVocalPc, ExamAudio.maxVocalPc);

      iNewPcValue = prompt(sInvite + "\n" + sAdvice, oElementPc.value);

      // Do not user !iNewValue which is also true for empty string    
      if (iNewPcValue == null) {
        return;
      }

      if (isNaN(iNewPcValue) || iNewPcValue < ExamAudio.minVocalPc || iNewPcValue > ExamAudio.maxVocalPc) {
        alert("Valeur incorrecte : " + iNewPcValue + "\n" + sAdvice);
        return;
      }
    }

    // Si une des deux valeurs est une chaîne vide, alors c'est une suppression de point
    if (iNewPcValue == "") {
      iNewDBValue = "";
    }
    else {
      iNewDBValue = Math.round(iNewDBValue);
    }

    if (iNewDBValue == "") {
      iNewPcValue = "";
    }
    else {
      iNewPcValue = Math.round(iNewPcValue);
    }

    oElementDB.value = iNewDBValue;
    oElementPc.value = iNewPcValue;

    return onSubmitFormAjax(oForm, {
      onComplete: ExamAudio.updateGraph.curry("audiometrie_vocale", sCote, old_exam_audio_id)
    });
  },

  // --- Tympanométrie
  minTympanAdmittance: 0,
  maxTympanAdmittance: 15,
  maxIndexPression: 7,

  /**
   * Changement des valeurs en fonction d'où clique l'utilisateur sur les graphs tympanométrie
   *
   * @param sCote
   * @param iPression
   * @param iNewValue
   * @param old_exam_audio_id
   */
  changeTympanValue: function (sCote, iPression, iNewValue, old_exam_audio_id) {
    old_exam_audio_id = (old_exam_audio_id) ? old_exam_audio_id : null;
    var oForm = getForm("editFrm");
    var sElementName = printf("_%s_tympan[%i]", sCote, iPression);
    var oElement = oForm.elements[sElementName];

    // Do not use !iNewValue which is also true for a 0 value    
    if (iNewValue == null) {
      var sPression = 100 * iPression - 400;
      var sInvite = printf("Modifier l'admittance pour l'oreille %s à la pression %s mm H²0", sCote, sPression);
      var sAdvice = printf("Merci de fournir une valeur comprise (en dixième de ml) entre %i et %i", ExamAudio.minTympanAdmittance, ExamAudio.maxTympanAdmittance);

      iNewValue = prompt(sInvite + "\n" + sAdvice, oElement.value);

      // Do not use !iNewValue which is also true for empty string    
      if (iNewValue == null) {
        return;
      }

      if (isNaN(iNewValue) || iNewValue < ExamAudio.minTympanAdmittance || iNewValue > ExamAudio.maxTympanAdmittance) {
        alert("Valeur incorrecte : " + iNewValue + "\n" + sAdvice);
        return;
      }
    }

    oElement.value = iNewValue;

    return onSubmitFormAjax(oForm, {
      onComplete: ExamAudio.updateGraph.curry("tympanometrie", sCote, old_exam_audio_id)
    });
  },

  /**
   * Ajout de la visualisation d'un ancien examen audio
   *
   * @param old_consultation_id
   * @param consultation_id
   * @param conduction
   * @param oreille
   */
  addOldExamaudio: function (old_consultation_id, consultation_id, conduction, oreille) {
    new Url("cabinet", "exam_audio")
      .addParam("old_consultation_id", old_consultation_id)
      .addParam("consultation_id", consultation_id)
      .addParam("_conduction", conduction)
      .addParam("_oreille", oreille)
      .requestUpdate("editFrm");

  },
  /**
   * Print the audiogram, hide some elements for printing and restore them
   */
  printExamAudio: function(){

    let class_name_remove = "radiointeractive",
      element_with_class = $("td_class_to_remove").removeClassName(class_name_remove),
      element_with_id = $("allvalues"),
      data_tonal = $("dataTonal-trigger").removeClassName("triggerHide"),
      data_vocal = $("dataVocal-trigger").removeClassName("triggerHide"),
      tympanometrie_gauche = $('examaudio-tympanometrie-gauche'),
      tympanometrie_droite = $('examaudio-tympanometrie-droite'),
      allvocales = $('allvocales'),
      title_print = $("title_print").show();

    element_with_id.removeClassName("form");
    element_with_id.removeAttribute('id');
    tympanometrie_gauche.style.display="block";
    tympanometrie_droite.style.display="block";
    allvocales.removeClassName("form");
    title_print.show();

    window.print();

    element_with_id.setAttribute('id','allvalues');
    element_with_id.addClassName("form");
    element_with_class.addClassName(class_name_remove);
    data_tonal.addClassName("triggerHide");
    data_vocal.addClassName("triggerHide");
    allvocales.addClassName("form");
    tympanometrie_gauche.style.display="inline-block";
    tympanometrie_droite.style.display="inline-block";
    title_print.hide();

    window.onfocus=function(){ window.close();}
  }
};
