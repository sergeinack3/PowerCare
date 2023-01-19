/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

function showQuestion(question){
  if(question){
    $('view'+question).show();
  }
}
function hideQuestion(question){
  if(question){
    var oForm = document.editFrmNyha;
    var oField = oForm[question];
    
    if(oField[0].checked || oField[1].checked){
      oField[2].checked = true;
      oField[0].onchange();
    }
    $('view'+question).hide();
  }
}

function changeValue(sField,sRepYes,sRepNo){
  var oForm = document.editFrmNyha;
  var oField = oForm[sField];

  if ($V(oField) == "1"){
    showQuestion(sRepYes);
    hideQuestion(sRepNo);
  }
  else {
    showQuestion(sRepNo);
    hideQuestion(sRepYes);
  }

  calculClasseNyha();
}

function calculClasseNyha(){
  var nyha = "";
  var oForm = document.editFrmNyha;
  if ($V(oForm.q1) == '1') {
    if ($V(oForm.q2a) == '0') {
      nyha = "Classe III";
    }
    if ($V(oForm.q2a) == '1' && $V(oForm.q2b) == '0') {
      nyha = "Classe II";
    }
    if ($V(oForm.q2a) == '1' && $V(oForm.q2b) == '1') {
      nyha = "Classe I";
    }
  }
  
  if ($V(oForm.q1) == '0') {
    if ($V(oForm.q3a) == '0') {
      nyha = "Classe III";
    }
    if ($V(oForm.q3a) == '1' && $V(oForm.q3b) == '0') {
      nyha = "Classe IV";
    }
    if ($V(oForm.q3a) == '1' && $V(oForm.q3b) == '1') {
      nyha = "Classe III";
    }
  }
  
  $('classeNyha').innerHTML = nyha;
}