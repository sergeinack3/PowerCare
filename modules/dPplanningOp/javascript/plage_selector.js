/**
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PlageOpSelector = {
  sForm               : null,  // Ici, on ne se sert pas de ce formulaire
  sPlage_id           : null,  // Identifiant de la plage
  sSalle_id           : null,  // Identifiant de la salle
  sDate               : null,  // Date de la plage
  sPlaceAfterInterv   : null,  // Id de l'interv après laquelle on veut placer
  sPlage_id_easy      : null,
  sSalle_id_easy      : null,
  sDateEasy           : null,
  s_hour_entree_prevue: null,
  s_min_entree_prevue : null,
  s_date_entree_prevue: null,
  new_dhe             : null,

  prepared: {
    plage_id          : null,
    sDate             : null,
    bAdm              : null,
    dAdm              : null,
    sHoraireVoulu     : null,
    sPlaceAfterInterv : null
  },
  
  options : {},

  pop: function(iChir, iTime_op, iGroup_id, iOperation_id, multiple, protocole_id) {
    if (checkChir() && checkDuree()) {
      new Url("planningOp", "plage_selector")
        .addParam("chir"        , iChir)
        .addParam("curr_op_time", iTime_op)
        .addParam("group_id"    , iGroup_id)
        .addParam("operation_id", iOperation_id)
        .addParam("multiple"    , multiple)
        .addParam("protocole_id", protocole_id)
        .addParam("new_dhe"     , this.new_dhe)
        .modal(this.options);
      //url.popup(this.options.width, this.options.height, "Plage");
    }
  },

  set: function(form, plage_id, salle_id, date, callback) {
    var list = form.list;
    if(date == '') {
      date = $V(form._date);
    }

    if(salle_id == '') {
      salle_id = $V(form._salle_id);
    }

    var place_after_interv_id = $V(form._place_after_interv_id);
    var horaire_voulu         = $V(form._horaire_voulu);
    var hour, hour_format     = '';

    if (!horaire_voulu) {
      var hour_selected = form.select("input[name=_place_after_interv_id]:checked")[0];

      if (hour_selected && hour_selected.get('heure')) {
        hour = hour_selected.get('heure').split('h');
        hour_format = hour[0] + ':' + hour[1] + ':00';
      }
    }

    if (plage_id == '') {
      plage_id = $V(form._plage_id);
    }

    if (!plage_id) {
      alert('Vous n\'avez pas selectionné de plage ou la plage selectionnée n\'est plus disponible à la planification.\n\nPour plus d\'information, veuillez contacter le responsable du bloc');
      return;
    }

    var adm = $V(form.admission);
    var typeHospi = "ambu";
    var hour_entree = $V(form.hour_jour);
    var min_entree  = $V(form.min_jour);

    // passage en hospi complete si admission == veille
    if(adm == "veille"){
      typeHospi = "comp";
      hour_entree = $V(form.hour_veille);
      min_entree  = $V(form.min_veille);
    }

    // Le close doit etre fait avant le doSet car il declenche une modale dans certains cas !!
    // S'il etait apres, ca serait l'autre modale qui serait fermée
    Control.Modal.close();

    // Declaration de formulaires
    var oOpForm     = getForm(this.new_dhe ? "operationEdit" : "editOp");
    var oSejourForm = getForm(this.new_dhe ? "sejourEdit" : "editSejour");
    
    if(!oSejourForm._duree_prevue.value) {
      oSejourForm._duree_prevue.value = 0;
    }

    if (hour_format) {
      $V(oOpForm._operation_plage_hour, hour_format);
    }
    
    if (plage_id) {
      if(oOpForm.plageop_id.value != plage_id) {
        oOpForm.rank.value = 0;
      }
           
      var dAdm = Date.fromDATE(date);
      // Initialize admission date according to operation date
      if (adm == "veille") {
        dAdm.addDays(-1);
      }

      var dateEntreeSej = $V(this.new_dhe ? oSejourForm.entree_prevue : oSejourForm._date_entree_prevue);
      var dateSortieSej = $V(this.new_dhe ? oSejourForm.sortie_prevue : oSejourForm._date_sortie_prevue);

      if(dateEntreeSej && dateSortieSej) {
        if (this.new_dhe) {
          dateEntreeSej = Date.fromDATETIME(dateEntreeSej);
          dateSortieSej = Date.fromDATETIME(dateSortieSej);
        }
        else {
          dateEntreeSej = Date.fromDATE(dateEntreeSej);
          dateSortieSej = Date.fromDATE(dateSortieSej);
        }

        if (!PlageOpSelector.new_dhe && adm == "aucune" && (dAdm > dateSortieSej || dAdm < dateEntreeSej) && !$("modeExpert").visible()){
          modalWindow = Modal.open($("date_alert"));
        }
      }
      
      if (adm != "aucune") {
        dAdm.setHours(hour_entree);
        dAdm.setMinutes(min_entree);

        if (this.new_dhe) {
          oSejourForm.entree_prevue_da.value = dAdm.toLocaleDateTime();
        }
        else {
          oSejourForm._date_entree_prevue_da.value = dAdm.toLocaleDate();
        }
      }
      
      oSejourForm._curr_op_date.value = date;
        
      if(typeHospi == "comp" && oSejourForm[this.sType].value=="ambu"){
        oSejourForm[this.sType].value = "comp";
      }
    }  
    
    // Sauvegarde des valeurs dans l'objet prepared
    this.prepared.dAdm              = dAdm;
    this.prepared.plage_id          = plage_id;
    this.prepared.salle_id          = salle_id;
    this.prepared.bAdm              = adm;
    this.prepared.sDate             = date;
    this.prepared.sPlaceAfterInterv = place_after_interv_id;
    this.prepared.sHoraireVoulu     = horaire_voulu;
    
    // Lancement de l'execution du set
    window.setTimeout( window.PlageOpSelector.doSet.curry(callback) , 1);
  },
  
  doSet: function(callback){
    var oOpForm     = getForm(PlageOpSelector.new_dhe ? "operationEdit" : "editOp");
    var oSejourForm = getForm(PlageOpSelector.new_dhe ? "sejourEdit" : "editSejour");
   
    $V(oOpForm[PlageOpSelector.sPlage_id]        , PlageOpSelector.prepared.plage_id);
    $V(oOpForm[PlageOpSelector.sSalle_id]        , PlageOpSelector.prepared.salle_id);
    $V(oOpForm[PlageOpSelector.sPlaceAfterInterv], PlageOpSelector.prepared.sPlaceAfterInterv);
    if(PlageOpSelector.prepared.sPlaceAfterInterv == '-1') {
      $V(oOpForm[PlageOpSelector.sHoraireVoulu], PlageOpSelector.prepared.sHoraireVoulu);
    } 
    
    // Si seul l'horaire voulu est changé, le onchange sur la date n'est pas fait.
    // Il faut donc le forcer
    $V(oOpForm[PlageOpSelector.sDate], "");
    $V(oOpForm[PlageOpSelector.sDate], PlageOpSelector.prepared.sDate);
   
    if(PlageOpSelector.prepared.bAdm != "aucune"){
      if (PlageOpSelector.new_dhe) {
        $V(oSejourForm[PlageOpSelector.s_date_entree_prevue], PlageOpSelector.prepared.dAdm.toDATETIME().replace("+", " "));
      }
      else {
        $V(oSejourForm[PlageOpSelector.s_hour_entree_prevue], PlageOpSelector.prepared.dAdm.getHours());
        $V(oSejourForm[PlageOpSelector.s_min_entree_prevue], PlageOpSelector.prepared.dAdm.getMinutes());
        $V(oSejourForm[PlageOpSelector.s_date_entree_prevue], PlageOpSelector.prepared.dAdm.toDATE());
      }
    }

    if (Object.isFunction(callback)) {
      callback();
    }
  }
};
