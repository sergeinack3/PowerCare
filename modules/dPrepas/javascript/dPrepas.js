/**
 * @package Mediboard\Repas
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

var odPrepas;
var saveInProgress = 0;
var sMsgAlertReturn = "Vous avez modifié des repas et ceux-ci n'ont pas été synchronisé.\nSi vous continuer, vous perdrez vos modifications.";

var ETAT_DEFAULT = 0;
var ETAT_SERV_RECUP = 16;
var ETAT_REPAS_RECUP = 32;
var ETAT_REPAS_MODIF = 48;
var ETAT_SYNCH = 64;

Main.add(function () {
  Calendar.regField(getForm("FrmSelectService").date);
});

function submitFormAjaxOffline(oForm, ioTarget, oOptions) {
  // the second test is only for IE
  if (oForm.attributes.onsubmit &&
    oForm.attributes.onsubmit.nodeValue &&
    !oForm.onsubmit()) {
    return;
  }

  oOptions = Object.extend({
    method: "post"
  }, oOptions);

  var url = new Url();
  url.mergeParams(serializeForm(oForm, oOptions));
  url.requestUpdateOffline(ioTarget, oOptions);
}

function storageMain() {
  loadDatadPrepas();
  //Initialisation pour le chargement
  if (config["etatOffline"] == ETAT_SYNCH) {
    setEtatOffline(0);
  } else {
    setEtatOffline(config["etatOffline"]);
  }
  if (odPrepas) {
    var iEtat = config["etatOffline"];
    if (iEtat == ETAT_SERV_RECUP) {
      createFormSelect();
    } else if (iEtat > ETAT_SERV_RECUP) {
      createPlanning();
    }
  }
}

function vwEtatButton(iEtat) {
  $('tdMenuRecupServ').className = "button";
  $('tdMenuRecupRepas').className = "button";
  $('tdMenuModifRepas').className = "button";
  $('tdMenuSynchro').className = "button";
  switch (iEtat) {
    case ETAT_DEFAULT:
    case ETAT_SERV_RECUP:
      $('tdMenuRecupServ').addClassName("iconSelected");
      break;
    case ETAT_REPAS_RECUP:
      $('tdMenuRecupRepas').addClassName("iconSelected");
      break;
    case ETAT_REPAS_MODIF:
      $('tdMenuModifRepas').addClassName("iconSelected");
      break;
    case ETAT_SYNCH:
      $('tdMenuSynchro').addClassName("iconSelected");
      break;
  }
}

function setEtatOffline(iEtat) {
  if (!odPrepas) {
    odPrepas = {
      config: {}
    };
  }
  vwEtatButton(iEtat);
  odPrepas["config"]["etatOffline"] = iEtat;
  Object.extend(config, odPrepas["config"]);
  MbStorage.save("dPrepas", odPrepas);
}

function verifEtatRequis(iEtatRequis) {
  var iEtatActuel = parseInt(odPrepas["config"]["etatOffline"]);
  iEtatRequis = parseInt(iEtatRequis);
  if (iEtatRequis > iEtatActuel) {
    return false;
  }
  return true;
}

function loadDatadPrepas() {
  odPrepas = MbStorage.load("dPrepas");
  if (odPrepas) {
    Object.extend(config, odPrepas["config"]);
  }
}

function createFormSelect(oData) {
  if (!oData) {
    oData = MbStorage.load("services");
  }
  var oServices = oData["oServices"];
  var oEtablissements = oData["oEtablissements"];
  var oSelectService = Dom.cloneElemById('templatelistService', true);
  $H(oEtablissements).each(function (pair) {
    var oGroup = pair.value;
    var iGroup_id = pair.key;
    var oOptGroup = document.createElement("optgroup");
    oOptGroup.setAttribute("label", oEtablissements[iGroup_id]["text"]);

    $H(oServices).each(function (pair) {
      var oService = pair.value;
      if (oService["group_id"] == iGroup_id) {
        Dom.createOptSelect(oService["service_id"], oService["nom"], false, oOptGroup);
      }
    });
    if (oOptGroup.hasChildNodes()) {
      oSelectService.appendChild(oOptGroup);
    }
  });

  Dom.writeElem('listService', oSelectService);
  $('divPlanningRepas').hide();
  $('divRepas').hide();
  $('vwServices').show();
  setEtatOffline(ETAT_SERV_RECUP);
}

Object.extend(AjaxResponse, {
  onDisconnected: function () {
    loginUrl = new Url;
    loginUrl.addParam("dialog", 1);
    loginUrl.pop(500, 300, "login", config["urlMediboard"] + "index.php");
  },
  storeData:      function (sNameKey, oDataSave) {
    MbStorage.save(sNameKey, oDataSave);
  },
  putServices:    function (sNameKey, oDataSave) {
    this.storeData(sNameKey, oDataSave);
    createFormSelect(oDataSave);
  },
  putdPrepasData: function (sNameKey, oDataSave) {
    oDataSave["oRepasNew"] = {};
    this.storeData(sNameKey, oDataSave);
    loadDatadPrepas();
    $('vwServices').hide();
    setEtatOffline(ETAT_REPAS_RECUP);
    createPlanning();
  },
  putRepas:       function (oRepas) {
    var iRepasId = oRepas["repas_id"];
    var affectation_id = oRepas["affectation_id"];
    var typerepas_id = oRepas["typerepas_id"];
    var iTmpRepasId = 0;

    if (odPrepas["oPlanningRepas"][affectation_id][typerepas_id]) {
      iTmpRepasId = odPrepas["oPlanningRepas"][affectation_id][typerepas_id]["_tmp_repas_id"];
      odPrepas["oRepas"][0][iTmpRepasId] = null;
    }
    odPrepas["oRepas"][iRepasId] = oRepas;
    odPrepas["oPlanningRepas"][affectation_id][typerepas_id]["_tmp_repas_id"] = 0;
    odPrepas["oPlanningRepas"][affectation_id][typerepas_id]["repas_id"] = iRepasId;
    this.storeData("dPrepas", odPrepas);
  }
});

//************************************

function loadServices() {
  var iEtatActuel = parseInt(odPrepas["config"]["etatOffline"]);
  if (iEtatActuel < ETAT_SYNCH && verifEtatRequis(ETAT_REPAS_MODIF)) {
    if (!confirm(sMsgAlertReturn)) {
      return false;
    }
  }
  //Retour autorisé apres alerte: Suppression des données pré-existante
  odPrepas = {}
  odPrepas["config"] = {};
  odPrepas["config"]["etatOffline"] = ETAT_DEFAULT;
  odPrepas["config"]["CRepas_modif"] = 0;
  Object.extend(config, odPrepas["config"]);
  MbStorage.save("dPrepas", odPrepas);
  //Non Affichage des DIV
  $('divPlanningRepas').hide();
  $('divRepas').hide();
  $('vwServices').hide();
  var url = new Url("dPhospi", "httpreq_get_services_offline");
  url.addParam("dialog", "1");
  url.requestUpdateOffline("systemMsg");
}

function getDatadPrepas() {
  var iEtatActuel = parseInt(odPrepas["config"]["etatOffline"]);
  var bRepasModif = verifEtatRequis(ETAT_REPAS_MODIF);
  var bRepasSynchro = verifEtatRequis(ETAT_SYNCH);

  if (!verifEtatRequis(ETAT_SERV_RECUP)) {
    return false;
  }
  if (iEtatActuel != ETAT_SERV_RECUP) {
    if (bRepasModif && !bRepasSynchro) {
      if (!confirm(sMsgAlertReturn)) {
        return false;
      }
      // Retour autorisé apres alerte : Suppression des données pré-existante
      odPrepas["config"]["CRepas_modif"] = 0;
    }
    createFormSelect();
    return false;
  }
  var oForm = document.FrmSelectService;
  if (!checkForm(oForm)) {
    return false;
  }
  var url = new Url("dPrepas", "httpreq_get_infos_offline");
  url.addParam("dialog", "1");
  url.addParam("service_id", oForm.service_id.value)
  url.addParam("date", oForm.date.value)
  url.requestUpdateOffline("systemMsg");
}

function synchrovalid(iRepasId) {
  // Synchonisation acceptée
  var oForm = document.editRepas;
  var oObjRepas = odPrepas["oRepas"][iRepasId];
  var sNameMsgId = oObjRepas["affectation_id"] + "_" + oObjRepas["typerepas_id"];
  Form.fromObject(oForm, oObjRepas);
  oForm._synchroConfirm.value = 1;
  submitFormAjaxOffline(oForm, sNameMsgId);
}

function synchroRefused(affectation_id, typerepas_id) {
  // Synchronisation refusée
  var oDivMsg = document.createElement("div");
  var oTextError = document.createTextNode("Le Repas n'a pas été envoyé.");
  oDivMsg.className = "error";
  oDivMsg.appendChild(oTextError);
  Dom.writeElem(affectation_id + "_" + typerepas_id, oDivMsg);
}

function checkInRepas() {
  var iEtatActuel = parseInt(odPrepas["config"]["etatOffline"]);
  if (iEtatActuel >= ETAT_SYNCH || !verifEtatRequis(ETAT_REPAS_MODIF)) {
    return false;
  }
  if ($('divPlanningRepas').style.display == "none") {
    alert("Veuillez terminer le repas en cours avant de synchroniser les données.");
    return false;
  }
  AjaxResponse.onLoaded = _submitRepas;
  var url = new Url("system", "empty");
  url.addParam("dialog", "1");
  url.requestUpdateOffline("systemMsg");
}

function _submitRepas() {
  AjaxResponse.onLoaded = Prototype.emptyFunction;
  var iEtatActuel = parseInt(odPrepas["config"]["etatOffline"]);
  if (iEtatActuel >= ETAT_SYNCH || !verifEtatRequis(ETAT_REPAS_MODIF)) {
    return false;
  }
  setEtatOffline(ETAT_SYNCH);
  var oForm = document.editRepas;
  var templateID = 'templateNoRepas';
  var vwListPlats = Dom.cloneElemById(templateID, true);
  var oRepasBdd = odPrepas["oRepas"];
  var oRepasNew = odPrepas["oRepasNew"];

  oForm.action = config["urlMediboard"];
  oForm._synchroConfirm.value = 0;

  // Permet d'ajouter des champs dans le formulaire
  Dom.cleanWhitespace(vwListPlats);
  var tbodyVwPlats = vwListPlats.childNodes[0];
  var linesPlats = tbodyVwPlats.childNodes;
  var inputMenuId = Dom.createInput("hidden", "menu_id", "");
  var thTitle = linesPlats[0].childNodes[0];
  thTitle.appendChild(inputMenuId);
  Dom.writeElem('listPlat', vwListPlats);

  $H(oRepasNew).each(function (pair) {
    var oObj = pair.value;
    if (pair.value != null) {
      Form.fromObject(oForm, oObj);
      var sNameMsgId = oObj["affectation_id"] + "_" + oObj["typerepas_id"];
      submitFormAjaxOffline(oForm, sNameMsgId);
    }
  });

  $H(oRepasBdd).each(function (pair) {
    var oObj = pair.value;
    if (pair.value != null) {
      Form.fromObject(oForm, oObj);
      var sNameMsgId = oObj["affectation_id"] + "_" + oObj["typerepas_id"];
      submitFormAjaxOffline(oForm, sNameMsgId);
    }
  });
}

function view_planning() {
  $('divPlanningRepas').show();
  $('divRepas').hide();
}

function saveRepas() {
  var oForm = document.editRepas;
  var oDataForm = new Object;
  oDataForm = Form.toObject(oForm);
  var affectation_id = oDataForm["affectation_id"];
  var typerepas_id = oDataForm["typerepas_id"];
  var elem = $(affectation_id + '_' + typerepas_id);

  // Mémorisation des informations
  if (oDataForm["repas_id"] != 0) {
    odPrepas["oRepas"][oDataForm["repas_id"]] = oDataForm;
  } else {
    var iTmpRepasId = oDataForm["_tmp_repas_id"];
    if (iTmpRepasId == 0) {
      // Création d'un repas
      var iTmpRepasId = (new Date).getTime();
      oDataForm["_tmp_repas_id"] = iTmpRepasId;
      odPrepas["oPlanningRepas"][affectation_id][typerepas_id]["_tmp_repas_id"] = iTmpRepasId;
      odPrepas["oPlanningRepas"][affectation_id][typerepas_id]["repas_id"] = 0;
    }
    if (oDataForm["del"] == 1) {
      oDataForm = null;
      odPrepas["oPlanningRepas"][affectation_id][typerepas_id]["_tmp_repas_id"] = 0;
      odPrepas["oPlanningRepas"][affectation_id][typerepas_id]["repas_id"] = 0;
    }
    odPrepas["oRepasNew"][iTmpRepasId] = oDataForm;
  }

  // Mémorisation des données

  odPrepas["config"]["CRepas_modif"] = 1;
  setEtatOffline(ETAT_REPAS_MODIF);

  // Etat dans le planning
  elem.innerHTML = "";
  viewEtatRepas(elem, affectation_id, typerepas_id);
  $('divPlanningRepas').show();
  $('divRepas').hide();
}

//Extraction des plats et plats de remplacements
function extractListPlat(oMenu, oRepas) {
  var oFields = {"plat1": null, "plat2": null, "plat3": null, "plat4": null, "plat5": null, "boisson": null, "pain": null};
  var iModifRepas = oMenu["modif"];
  var oPlats = odPrepas["oPlats"];

  for (key in oFields) {
    oFields[key] = document.createElement("optgroup");
    oFields[key].setAttribute("label", "Remplacements possibles");
  }

  if (iModifRepas == 1) {
    $H(oPlats).each(function (pair) {
      var oPlatRemplacement = pair.value;
      if (oPlatRemplacement["typerepas"] == oMenu["typerepas"]) {
        var bSelectedObj = false;
        var iTypePlat = oPlatRemplacement["type"];
        if (oRepas && oRepas[iTypePlat] == oPlatRemplacement["plat_id"]) {
          bSelectedObj = true;
        }
        Dom.createOptSelect(oPlatRemplacement["plat_id"], oPlatRemplacement["nom"], bSelectedObj, oFields[iTypePlat]);
      }
    });
  }

  for (key in oFields) {
    var oOptgroup = oFields[key];
    if (oOptgroup.hasChildNodes()) {
      var oSelectObj = Dom.createSelect(key);
      var bSelectedObj = false;
      if (oRepas && (oRepas[key] == "" || oRepas[key] == null)) {
        bSelectedObj = true;
      }
      Dom.createOptSelect("", oMenu[key], bSelectedObj, oSelectObj);
      oSelectObj.appendChild(oFields[key]);
      oFields[key] = oSelectObj;
    } else {
      // Ajouter un champ hidden pour le plat
      var oInputplat = Dom.createInput("hidden", key, "");
      var oNamePlat = document.createTextNode(oMenu[key]);
      oInputplat.appendChild(oNamePlat);
      oFields[key] = oInputplat;
    }
  }
  return oFields;
}

function vwListMenu(typerepas_id, repas_id, tmp_repas_id) {
  var oListMenus = odPrepas["oMenus"][typerepas_id];

  var oVwListMenus = Dom.cloneElemById('templateListRepas', true);
  var oLineVwMenus = oVwListMenus.childNodes;
  var oLigneMenu = document.createElement("tr");
  var oCelluleMenu = Dom.createTd("button");
  var oCelluleNomMenu = Dom.createTd("text");

  oLigneMenu.appendChild(oCelluleNomMenu);
  var aNameCellule = new Array("diabete", "sans_sel", "sans_residu");

  for (i = 1; i <= aNameCellule.length; i++) {
    var oCloneCell = oCelluleMenu.cloneNode(false)
    oLigneMenu.appendChild(oCloneCell);
  }

  $H(oListMenus).each(function (pair) {
    var oLine = oLigneMenu.cloneNode(true);
    var oMenu = pair.value;
    var oChildsLine = oLine.childNodes;
    oChildsLine.item(0).innerHTML = "<a href='#' onclick='vwPlats(" + oMenu["_id"] + ")'>" + oMenu["nom"] + "</a>";

    for (i = 1; i <= aNameCellule.length; i++) {
      var sTextNode = "";
      if (oMenu[aNameCellule[i]] == 1) {
        sTextNode = "<strong>Oui</strong>";
      }
      oChildsLine.item(i).innerHTML = sTextNode;
    }
    oLineVwMenus.item(1).appendChild(oLine);
  });
  Dom.writeElem('tdlistMenus', oVwListMenus);
}

function vwPlats(menu_id) {
  var oForm = document.editRepas;
  var repas_id = oForm.repas_id.value;
  var tmp_repas_id = oForm._tmp_repas_id.value;
  var typerepas_id = oForm.typerepas_id.value;
  var del = oForm._del.value;
  var oRepasBdd = odPrepas["oRepas"];
  var oRepasNew = odPrepas["oRepasNew"];
  var oMenus = odPrepas["oMenus"];

  if (repas_id != 0) {
    var oRepas = oRepasBdd[repas_id];
  }
  if (tmp_repas_id != 0) {
    var oRepas = oRepasNew[tmp_repas_id];
  }
  if (menu_id == "" || menu_id == null) {
    var templateID = 'templateNoRepas';
  } else {
    var templateID = 'templateListPlats';
  }

  var vwListPlats = Dom.cloneElemById(templateID, true);
  Dom.cleanWhitespace(vwListPlats);
  var tbodyVwPlats = vwListPlats.childNodes[0];
  var linesPlats = tbodyVwPlats.childNodes;
  var inputMenuId = Dom.createInput("hidden", "menu_id", menu_id);
  var thTitle = linesPlats[0].childNodes[0];

  if (menu_id != "" && menu_id != null) {
    if (oRepas) {
      var oListMenus = oMenus[oRepas["typerepas_id"]];
    } else if (typerepas_id) {
      var oListMenus = oMenus[typerepas_id];
    }

    var oMenu = oListMenus[menu_id];
    var sMenuName = document.createTextNode(oMenu["nom"]);
    thTitle.appendChild(sMenuName);

    // Ecriture des plats et plats remplacements
    var aSelect = extractListPlat(oMenu, oRepas);
    for (i = 1; i < linesPlats.length; i++) {
      var oLinePlatEnCours = linesPlats[i].childNodes[1];
      var sNameTypePlat = oLinePlatEnCours.getAttribute("id");
      oLinePlatEnCours.appendChild(aSelect[sNameTypePlat]);
    }
  }

  thTitle.appendChild(inputMenuId);

  // Ecriture des boutons du formulaire
  var trButton = document.createElement("tr");
  var tdButton = Dom.createTd("button", "2");
  if (del == 0 && (repas_id != 0 || tmp_repas_id != 0)) {
    // modification
    var buttonMod = Dom.cloneElemById('templateButtonMod', true);
    var buttonDel = Dom.cloneElemById('templateButtonDel', true);
    tdButton.appendChild(buttonMod);
    tdButton.appendChild(buttonDel);
  } else {
    var buttonAdd = $('templateButtonAdd').cloneNode(true);
    tdButton.appendChild(buttonAdd);
  }
  trButton.appendChild(tdButton);
  vwListPlats.appendChild(trButton);

  Dom.writeElem('listPlat', vwListPlats);
}

function vwRepas(affectation_id, typerepas_id) {
  if (!verifEtatRequis(ETAT_REPAS_RECUP) || verifEtatRequis(ETAT_SYNCH)) {
    return false;
  }
  var repas = odPrepas["oPlanningRepas"][affectation_id][typerepas_id];
  var oRepasBdd = odPrepas["oRepas"];
  var oRepasNew = odPrepas["oRepasNew"];
  var oAffectation = odPrepas["oAffectations"][affectation_id];
  var oType = odPrepas["oListTypeRepas"][typerepas_id];
  var oForm = document.editRepas;
  var oButtonBack = Dom.cloneElemById('templateHrefBack', true);
  var oConfigdPrepas = odPrepas["config"];

  oForm.repas_id.value = repas["repas_id"];
  oForm._tmp_repas_id.value = repas["_tmp_repas_id"];
  oForm.typerepas_id.value = typerepas_id;
  oForm.affectation_id.value = affectation_id;
  oForm.del.value = 0;
  oForm._del.value = 0;
  oForm._synchroConfirm.value = 0;
  oForm.date.value = oConfigdPrepas["CRepas_date"];
  var sDate = oConfigdPrepas["CRepas_date"].substr(8, 2)
    + " / " + oConfigdPrepas["CRepas_date"].substr(5, 2)
    + " / " + oConfigdPrepas["CRepas_date"].substr(0, 4);
  if (typeof repas != "object") {
    return;
  }
  $('divPlanningRepas').hide();
  if (repas["repas_id"] != 0) {
    var oRepas = oRepasBdd[repas["repas_id"]];
  }
  if (repas["_tmp_repas_id"] != 0) {
    var oRepas = oRepasNew[repas["_tmp_repas_id"]];
  }
  if (oRepas) {
    oForm._del.value = oRepas["del"];
  }
  if (oRepas && oRepas["del"] == 0) {
    // Repas existant et non supprimé
    vwPlats(oRepas["menu_id"]);
    var oTextThTitle = document.createTextNode("Modification d'un repas");
    $('thRepasTitle').className = "title modify"
  } else {
    Dom.writeElem('listPlat');
    var oTextThTitle = document.createTextNode("Enregistrement d'un repas");
    $('thRepasTitle').className = "title"
  }
  Dom.writeElem('thRepasTitle', oButtonBack);
  $('thRepasTitle').appendChild(oTextThTitle);

  vwListMenu(typerepas_id, repas["repas_id"], repas["_tmp_repas_id"]);
  $('tdRepasChambre').innerHTML = oAffectation["_view"];
  $('tdRepasTypeRepas').innerHTML = oType["nom"];
  $('tdRepasDate').innerHTML = sDate;
  $('divRepas').show();
}

//Fonction d'ecriture de l'état d'un repas pour une affectation et un type de repas
function viewEtatRepas(elem, affectation_id, typerepas_id) {
  // Récupération des repas
  var repas = odPrepas["oPlanningRepas"][affectation_id][typerepas_id];
  var oRepasBdd = odPrepas["oRepas"];
  var oRepasNew = odPrepas["oRepasNew"];

  // Création des différents état de Repas
  var imgRepasPlanifie = Dom.createImg("images/icons/tick-dPrepas.png");
  var imgNoRepas = Dom.createImg("images/icons/no.png");
  var imgRepasFlag = Dom.createImg("images/icons/flag.png");

  if (typeof repas == "object") {
    var urlimg = document.createElement("a");
    urlimg.setAttribute("href", "#");
    urlimg.setAttribute("onclick", "vwRepas('" + affectation_id + "','" + typerepas_id + "')");

    if (repas["repas_id"] == 0 && repas["_tmp_repas_id"] == 0) {
      // Non plannifié
      urlimg.appendChild(imgRepasFlag);
    } else {
      if (repas["repas_id"] != 0) {
        var oRepas = oRepasBdd[repas["repas_id"]];
      }
      if (repas["_tmp_repas_id"] != 0) {
        var oRepas = oRepasNew[repas["_tmp_repas_id"]];
      }

      if (oRepas && oRepas["del"] == 1) {
        urlimg.appendChild(imgRepasFlag);
      } else if (oRepas && (oRepas["menu_id"] == "" || oRepas["menu_id"] == null)) {
        urlimg.appendChild(imgNoRepas);
      } else {
        urlimg.appendChild(imgRepasPlanifie);
      }
    }
    elem.appendChild(urlimg);
  } else {
    // Ne pas planifié de repas ici
    elem.innerHTML = '-';
  }
}

//Fonction d'ecriture du planning pour 1 jour et 1 service donné
function createPlanning() {
  var oTypeRepas = odPrepas["oListTypeRepas"];
  var oAffectations = odPrepas["oAffectations"];
  var oSejours = odPrepas["oSejours"];
  var oPatients = odPrepas["oPatients"];
  var oConfig = odPrepas["config"];

  var oTblPlanning = document.createElement("table");
  oTblPlanning.className = "tbl";
  oTblPlanning.setAttribute("id", "tablePlanning");

  // Création de la ligne vide
  var oEmptyLine = document.createElement("tr");
  var oFirstLine = oEmptyLine.cloneNode(false);
  var oEmptyTD = document.createElement("td");
  var oEmptyTH = document.createElement("th");

  oEmptyTH.className = "category";

  oEmptyLine.appendChild(oEmptyTD.cloneNode(false));
  oEmptyLine.appendChild(oEmptyTD.cloneNode(false));
  oFirstLine.appendChild(oEmptyTD.cloneNode(false));
  oFirstLine.appendChild(oEmptyTD.cloneNode(false));

  $H(oTypeRepas).each(function (pair) {
    var typeRepas = pair.value;
    // Ligne vide
    var oCelluleTD = oEmptyTD.cloneNode(false);
    oCelluleTD.setAttribute("id", typeRepas["_id"]);
    oEmptyLine.appendChild(oCelluleTD);
    // Premiere ligne
    var oCelluleTH = oEmptyTH.cloneNode(false);
    oCelluleTH.innerHTML = typeRepas["nom"];
    oFirstLine.appendChild(oCelluleTH);
  });

  // Information service et date
  var oTrInformation = document.createElement("tr");
  var oTdInformation = Dom.createTh("title", oFirstLine.childNodes.length);
  var oServices = MbStorage.load("services");
  var oService = oServices["oServices"][oConfig["CRepas_service_id"]];
  var sDate = oConfig["CRepas_date"].substr(8, 2)
    + " / " + oConfig["CRepas_date"].substr(5, 2)
    + " / " + oConfig["CRepas_date"].substr(0, 4);

  oTdInformation.innerHTML = oService["nom"] + " le " + sDate;
  oTrInformation.appendChild(oTdInformation);

  oTblPlanning.appendChild(oTrInformation);
  oTblPlanning.appendChild(oFirstLine);

  $H(oAffectations).each(function (pair) {
    var oLine = oEmptyLine.cloneNode(true);
    var oCurrentAffect = pair.value;
    if (oLine.hasChildNodes()) {
      // Récupération des informations necessaires
      var oSejour = oSejours[oCurrentAffect["sejour_id"]];
      var oPatient = oPatients[oSejour["patient_id"]];

      var oChildsLine = oLine.childNodes;
      oChildsLine.item(0).innerHTML = oCurrentAffect["_view"];
      oChildsLine.item(1).innerHTML = oPatient["_view"];

      if (oChildsLine.length > 2) {
        for (var i = 2; i < oChildsLine.length; i++) {
          var elem = oChildsLine.item(i);
          var typerepasid = elem.getAttribute("id");
          elem.className = "button";
          elem.setAttribute("id", oCurrentAffect["_id"] + "_" + typerepasid);
          viewEtatRepas(elem, oCurrentAffect["_id"], typerepasid);
        }
      }
    }
    oTblPlanning.appendChild(oLine);
  });

  Dom.writeElem('divPlanningRepas', oTblPlanning);
  $('divPlanningRepas').show();
  $('divRepas').hide();
}