/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

function calculGlasgow(){
  var oForm = document.editFrmPossum;
  var glasgow = 0;
  
  if(oForm.ouverture_yeux.value!=""){
    glasgow = glasgow + listScorePhysio["ouverture_yeux"][oForm.ouverture_yeux.value];
  }
  if(oForm.rep_verbale.value!=""){
    glasgow = glasgow + listScorePhysio["rep_verbale"][oForm.rep_verbale.value];
  }
  if(oForm.rep_motrice.value!=""){
    glasgow = glasgow + listScorePhysio["rep_motrice"][oForm.rep_motrice.value];
  }
  return glasgow;
}

function calculPhysio(){
  var oForm = document.editFrmPossum;
  scorePhysio = 0;
  for (var elm in listScorePhysio) {
    if (typeof(listScorePhysio[elm]) != "function") { // to filter prototype functions
      if(elm == "rep_motrice"){
        glasgow = calculGlasgow();
        if(glasgow >=1 && glasgow <= 8){
          scorePhysio += 8;
        }else if(glasgow >= 9 && glasgow <= 11){
          scorePhysio += 4;
        }else if(glasgow >= 12 && glasgow <= 14){
          scorePhysio += 2;
        }else if(glasgow == 15){
          scorePhysio += 1;
        }
      }else if(elm != "ouverture_yeux" && elm != "rep_verbale"){
        oField = oForm[elm];
        if(oField && oField.value!=""){
          scorePhysio += listScorePhysio[elm][oField.value];
        }
      }
    }
  }
  $('score_physio').innerHTML = scorePhysio;
  calculPossum();
}


function calculOper(){
  var oForm = document.editFrmPossum;
  scoreOper = 0;
  for (var elm in listScoreOper) {
    if (typeof(listScoreOper[elm]) != "function") { // to filter prototype functions
      oField = oForm[elm];
      if(oField && oField.value!=""){
        scoreOper += listScoreOper[elm][oField.value];
      }
    }
  }
  $('score_oper').innerHTML = scoreOper;
  calculPossum();
}


function Fmt(x) {
  var v = '' + (x >= 0 ? (x+0.05) : (x-0.05));
  return v.substring(0,v.indexOf('.')+2);
}

function calculPossum(){
  var morbidite;
  var mortalite;
  
  var formule_morb = (0.16 * scorePhysio) + (0.19 * scoreOper)- 5.91;
  var formule_mort = (0.13 * scorePhysio) + (0.16 * scoreOper)- 7.04;

  morbidite = 1/(1+Math.exp(-formule_morb));
  mortalite = 1/(1+Math.exp(-formule_mort));
  
  $('morbidite').innerHTML = Fmt(morbidite * 100) + " %";
  $('mortalite').innerHTML = Fmt(mortalite * 100) + " %";
}