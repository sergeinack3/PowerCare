{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">

window.oCcamField = null;

function updateTokenCcam(){
  refreshListCCAM("expert");
  refreshListCCAM("easy");
  $V(document.editOp._codes_ccam, "");
  modifOp();
}

function refreshListCCAM(mode) {
  if (mode=="expert") {
    var oCcamNode = $("listCodesCcam");
  }
  if (mode=="easy") {
    var oCcamNode = $("listCodesCcamEasy");
  }
  var oForm = document.editOp;
  var aCcam = oForm.codes_ccam.value.split("|").without("");

  var aCodeNodes = new Array();
  aCcam.each(function(sCode) {
    if (sCode.indexOf('*') != -1) {
      var count = sCode.substring(0, sCode.indexOf('*'));
      var sCode = sCode.substring(sCode.indexOf('*') + 1);

      for (var i = 0; i < count; i++) {
        var sCodeNode = mode == "expert" ?
          printf('<button class="remove" type="button" onclick="{{if !$op->_id}}removePrecodageCCAM(\'%s\'); {{/if}}oCcamField.remove(\'%s\', true)">%s</button>', sCode, sCode, sCode) : sCode;

        aCodeNodes.push(sCodeNode);
      }
    }
    else {
      var sCodeNode = mode == "expert" ?
        printf('<button class="remove" type="button" onclick="{{if !$op->_id}}removePrecodageCCAM(\'%s\'); {{/if}}oCcamField.remove(\'%s\', true)">%s</button>', sCode, sCode, sCode) : sCode;

      aCodeNodes.push(sCodeNode);
    }
  });
  oCcamNode.innerHTML = aCodeNodes.join(mode == "easy" ? " &mdash; " : "");
  periodicalTimeUpdater.currentlyExecuting = false;
  {{if !$op->_id}}
    updatePrecodageCCAM();
  {{/if}}
}

function updateTime() {
  var oForm = document.editOp;

  if(oForm.chir_id.value) {
    new Url("planningOp", "httpreq_get_op_time")
      .addElement(oForm.chir_id)
      .addElement(oForm.codes_ccam, "codes")
      .requestUpdate("timeEst");
    
    new Url("planningOp", "httpreq_get_hospi_time")
      .addElement(oForm.chir_id)
      .addElement(oForm.codes_ccam, "codes")
      .requestUpdate("dureeEst");
  }
}

function checkFormOperation() {
  var oForm = document.editOp;
  return checkForm(oForm) && checkDuree() && checkCCAM() && checkCompatOpAdm();
}

function checkCCAM() {
  var oForm = document.editOp;
  var sCcam = $V(oForm._codes_ccam);
  if(sCcam != "") {
    if(!oCcamField.add(sCcam,true)) {
      return false;
    }
  }
  if (oCcamField.contains("XXXXXX")) {
    oCcamField.remove("XXXXXX");
  }
  var sCodesCcam = oForm.codes_ccam.value;
  var sLibelle = oForm.libelle.value;
  if(sCodesCcam == "" && sLibelle == "") {
    alert("Vous indiquez un acte ou remplir le libellé");
    oForm.libelle.focus();
    return false
  }
  return true;
}

function checkChir() {
  var oForm = document.editOp;
  var oField = null;
  
  if (oField = oForm.chir_id) {
    if (oField.value == 0) {
      alert("Chirurgien manquant");
      return false;
    }
  }
  return true;
}

function checkDuree() {
  var form = document.editOp;
  var field1 = form._time_op;

  if (field1 && field1.value == "00:00:00") {
    alert("Temps opératoire invalide");
    return false;
  }

  return true;
}

function checkCompatOpAdm() {
  var oOpForm     = document.editOp;
  var oSejourForm = document.editSejour;
  // cas des urgences
  if(oOpForm.date.value && oSejourForm._date_entree_prevue.value) {
    if(oOpForm.date.value < oSejourForm._date_entree_prevue.value) {
      alert("Date d'admission superieure à la date d'intervention");
      oSejourForm._date_entree_prevue.focus();
      return false;
    }
  }
  // cas normal
  if(oOpForm._date.value && oSejourForm._date_entree_prevue.value) {
    if(oOpForm._date.value < oSejourForm._date_entree_prevue.value) {
      alert("Date d'admission superieure à la date d'intervention");
      oSejourForm._date_entree_prevue.focus();
      return false;
    }
  }
  return true;
}

function modifOp() {
  modifSejour();
}

function synchroPrat() {
  var oOpForm = document.editOp;
  var oSejourForm = document.editSejour;
  if (oOpForm.chir_id.value == 0) {
    oOpForm.chir_id.value = '';
    oOpForm.chir_id_view.value = '';
  }
  if (!oSejourForm.sejour_id.value) {
    $V(oSejourForm.praticien_id, oOpForm.chir_id.value);
    $V(oSejourForm.praticien_id_view, oOpForm.chir_id_view.value);
  }
  updateTime();
}

function updateDureePostop() {
  var form = document.editOp;
  var type_anesth_id = $V(form.type_anesth);
  if (type_anesth_id) {
    var listType = [];
    {{foreach from=$listAnesthType item=_anesth}}
      listType['{{$_anesth->_id}}'] = '{{$_anesth->duree_postop}}';
    {{/foreach}}
    var duree = listType[type_anesth_id];
    $V(form.duree_postop, duree);
    $V(form.duree_postop_da, duree.substr(0, 5));
  }
}

function updateEntreePrevue() {
  var oOpForm = document.editOp;
  var oSejourForm = document.editSejour;
    
  if(!oSejourForm._duree_prevue.value) {
    oSejourForm._duree_prevue.value = 0;
  }

  if(oOpForm.date.value) {
    if(!oSejourForm._date_entree_prevue.value || !(oSejourForm._date_entree_prevue.value <= oOpForm.date.value && oSejourForm._date_sortie_prevue.value >= oOpForm.date.value)) {
      oSejourForm._date_entree_prevue.value = oOpForm.date.value;
      oView = getForm('editSejour')._date_entree_prevue_da;
      oView.value = Date.fromDATE(oOpForm.date.value).toLocaleDate();
    }
  }
  
  updateSortiePrevue();
}

CanBloc = {{$modules.dPbloc->_can|json}};

function cancelOperation() {
  var oForm = document.editOp;
  var oElement = oForm.annulee;
  
  if (oElement.value == "0") {
    var today = new Date().toDATE();

    // Tester supérieur à 0 semble obligatoire
    if (oForm._count_actes.value > 0 && oForm._date.value <= today) {
      var msg = "Attention, l'intervention a probablement déjà eu lieu.\n\n";
  
      if (CanBloc.edit) {
        if (!confirm(msg + "Voulez-vous malgré tout l'annuler ?")) {
          return;
         }
      }
      else {
        alert(msg + "Veuillez vous adresser au responsable de bloc pour annuler cette intervention");
        return;
      }
    }
    var annuler_sejour = false;
    if (confirm("Voulez-vous vraiment annuler l'intervention ?")) {
      if (confirm("Souhaitez-vous annuler le Séjour correspondant ?\n\nATTENTION, cette action annulera toutes les interventions de ce séjour !")) {
        annuler_sejour = true;
      }
      
      {{if $conf.dPplanningOp.COperation.cancel_only_for_resp_bloc && $modules.dPbloc->_can->edit && $op->_id && $op->_ref_sejour->entree_reelle && $op->rank}}
        // Si annulation d'une intervention validée que par le chef de bloc
        // alors, complétion des remarques pour ajouter Récusée
        var rques = $V(oForm.rques);
        if (rques) {
          $V(oForm.rques, "Récusée\n" + rques);
        }
        else {        
          $V(oForm.rques,  "Récusée");
        }
      {{/if}}

      if (annuler_sejour) {
        Modal.open("tooltip_annulation_sejour");
      } else {
        oElement.value = "1";
        submitForms();
      }

      return;
    }
  }
      
  if (oElement.value == "1") {
    var txtalert = "";
    if(document.editSejour.annule.value == 1){
      txtalert = "\n\n ATTENTION ! Cette intervention va rétablir le séjour choisi.";
    }      
    if (confirm("Voulez-vous vraiment rétablir l'intervention ?" + txtalert)) {
      oElement.value = "0";
      oForm.submit();
      return;
    }
  }
}
  
var periodicalTimeUpdater = null;
  
function incFormOperationMain() {
  periodicalTimeUpdater = new PeriodicalExecuter(updateTime, 1);

  refreshListCCAM("expert");
  refreshListCCAM("easy");
  
  {{if $modurgence && !$op->operation_id && !$sejour->entree_reelle}}
    updateEntreePrevue();
  {{/if}}
  
  oCcamField = new TokenField(document.editOp.codes_ccam, { 
    onChange : updateTokenCcam,
    sProps : "notNull code ccam",
    serialize: true
  } );
}

function toggleOtherPrats(elt) {
  var form = getForm("editOp");
  var formEasy = getForm("editOpEasy");
  form.select('.other_prats').invoke('toggle');
  formEasy.select('.other_prats').invoke('toggle');
  Element.classNames(form.chir_id.next('button')).flip('up', 'down');
  Element.classNames(formEasy.chir_id.next('button')).flip('up', 'down');
}

Main.add(incFormOperationMain);

{{if !$op->_id}}
  updatePrecodageCCAM = function() {
    var oForm = getForm('editOp');
    $('listCodageCCAM_chir').update('');
    if ($V(oForm._codage_ccam_chir) != '') {
      var codageCCAM = $V(oForm._codage_ccam_chir).split('|');
      codageCCAM.each(function (codage) {
        codage = codage.split('-');
        var span = DOM.span({
          class: codage[0]
        }, codage[0]);
        span.insert(DOM.span({class: 'circled'}, codage[1] + '-' + codage[2]));
        codage[3].toArray().each(function (mod) {
          span.insert(DOM.span({class: 'circled'}, mod));
        });
        $('listCodageCCAM_chir').insert(span);
      });
    }

    $('listCodageCCAM_anesth').update('');
    if ($V(oForm._codage_ccam_anesth) != '') {
      var codageCCAM = $V(oForm._codage_ccam_anesth).split('|');
      codageCCAM.each(function (codage) {
        codage = codage.split('-');
        var span = DOM.span({
          class: codage[0]
        }, codage[0]);
        span.insert(DOM.span({class: 'circled'}, codage[1] + '-' + codage[2]));
        codage[3].toArray().each(function (mod) {
          span.insert(DOM.span({class: 'circled'}, mod));
        });
        $('listCodageCCAM_anesth').insert(span);
      });
    }

    if ($V(oForm.codes_ccam) != '') {
      $('listCodageCCAM_chir').insert(DOM.button({
        className: 'edit notext',
        type: 'button',
        onclick: 'precodeCCAM("chir")'
      }, 'Codage CCAM chir'));

      $('listCodageCCAM_anesth').insert(DOM.button({
        className: 'edit notext',
        type: 'button',
        onclick: 'precodeCCAM("anesth")'
      }, 'Codage CCAM anesth'));
    }
  };

  removePrecodageCCAM = function(code) {
    var form = getForm("editOp");
    var old_codage_chir = $V(form._codage_ccam_chir);
    var new_codage_chir = old_codage_chir.split('|');
    old_codage_chir.split('|').each(function(codage, index) {
      if (codage.search(code) != -1) {
        new_codage_chir.splice(index, 1);
        throw $break;
      }
    });

    var old_codage_anesth = $V(form._codage_ccam_anesth);
    var new_codage_anesth = old_codage_anesth.split('|');
    old_codage_anesth.split('|').each(function(codage, index) {
      if (codage.search(code) != -1) {
        new_codage_anesth.splice(index, 1);
        throw $break;
      }
    });

    $V(form._codage_ccam_chir, new_codage_chir.join('|'));
    $V(form._codage_ccam_anesth, new_codage_anesth.join('|'));
  };

  precodeCCAM = function(role) {
    var form = getForm('editOp');
    var url = new Url('planningOp', 'ajax_codage_protocole');
    url.addParam('codes_ccam', $V(form.codes_ccam));
    if ($V(form.chir_id)) {
      url.addParam('chir_id', $V(form.chir_id));
    }
    else {
      Modal.alert('Veuillez sélectionner un praticien avant de faire le précodage des actes');
      return;
    }
    if ($V(form.anesth_id)) {
      url.addParam('anesth_id', $V(form.anesth_id));
    }
    if ($V(form._codage_ccam_chir)) {
      url.addParam('codage_ccam_chir', $V(form._codage_ccam_chir));
    }
    if ($V(form._codage_ccam_anesth)) {
      url.addParam('codage_ccam_anesth', $V(form._codage_ccam_anesth));
    }

    url.addParam('role', role);
    url.addParam('object_class', 'COperation');

    url.requestModal(-10, -50, {
      showClose: 0,
      showReload: 0,
      method: 'post',
      getParameters: {m: 'planningOp', a: 'ajax_codage_protocole'}
    });
  };
{{/if}}
</script>
