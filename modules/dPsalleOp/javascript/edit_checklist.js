/**
 * @package Mediboard\SalleOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

EditDailyCheck = {
  checkListTypes: ["normal", "endoscopie", "endoscopie-bronchique", "radio", "cesarienne", "normal_ch", "normal_2018"],
  HAS_classes: null,
  preview: null,
  dToday: null,
  showCheckListType: function(element, type) {
    EditDailyCheck.checkListTypes.each(function(t){
      element.select('tr.'+t).invoke("hide");
    });
    element.select('tr.'+type).invoke("show");

    $('asterisque_checklist')[(type === 'endoscopie' || type === 'endoscopie-bronchique') ? 'hide' : 'show']();
  },
  checkReloadLocation:  function(form) {
    if (EditDailyCheck.HAS_classes.indexOf($V(form.object_class)) == -1 &&
      $V(form.ref_type_list) != "fermeture_salle" && $V(form.ref_type_list) != "fermeture_sspi" && $V(form.ref_type_list) != "fermeture_preop" &&
      !(($V(form.multi_ouverture) == "true" || $V(form.multi_ouverture) == "1") && $V(form.ref_type_list) == "ouverture_salle") &&
      (($V(form.ref_type_list) != "ouverture_sspi" && $V(form.ref_type_list) != "ouverture_preop") || $V(form.choose_moment_edit) == 0)) {
      return true;
    }
    return false;
  },
  refreshCheckListValidate: function(check_list_type, list_type_id, check_list_id) {
    var url = new Url('dPsalleOp', 'httpreq_vw_check_list');
    url.addParam('check_list_id', check_list_id);
    url.requestUpdate('check_list_'+check_list_type+'_'+list_type_id);
  },
  confirmationSignature: function(form) {
    if (confirm($T('CDailyCheckList-Have all points been verified ?'))) {
      $V(form._signature, 1);
      return onSubmitFormAjax(form, {
        onComplete: function () {
          if (EditDailyCheck.checkReloadLocation(form)) {
            location.reload();
          }
        }
      });
    }
  },
  changeValidator: function(element, user_id) {
    var input = $(element.form.name+'__validator_password');
    $V(input, '');
    if ($V(element) == user_id) {
      input.removeClassName('notNull');
      input.hide();
      return;
    }
    input.addClassName('notNull');
    input.show();
  },
  changeGoIncision: function(form, changeValue) {
    var result_nogo = form.result_nogo;
    var retard = result_nogo[0];
    var annulation = result_nogo[1];
    switch($V(form.decision_go)) {
      case "go":
        retard.disabled = 'disabled';
        annulation.disabled = 'disabled';
        $V(form.result_nogo, '');
        break;
      case "nogo":
        retard.disabled = '';
        annulation.disabled = '';
        break;
      default:
        break;
    }
    if (changeValue) {
      EditDailyCheck.submitCheckList(form, true);
    }
  },
  submitCheckList: function(form, quicksave) {
    if (EditDailyCheck.preview) {
      return;
    }
    if (!quicksave) {
      return EditDailyCheck.confirmCheckList(form, EditDailyCheck.dToday);
    }

    $V(form._validator_password, "");

    return onSubmitFormAjax(form, {
      check: function(){return true}
    });
  },
  confirmCheckList: function(form) {
    var confirmation = 1;
    $V(form._signature, 0);
    if ($V(form.date) != 'now' && $V(form.date) != EditDailyCheck.dToday) {
      confirmation = confirm($T("CDailyCheckList-The validation date is different from the current date") + '\n' + $T("CDailyCheckList-Do you really want to confirm the validation ?"));
    }

    if (confirmation) {
      return checkForm(form) && EditDailyCheck.confirmationSignature(form);
    }
    else {
      return false;
    }
  },
  seeCommentaire: function(form, curr_type_id, type) {
    var checkbox = $(form.name+'_'+'_items['+curr_type_id+'_use_comment]');
    var commentaire = $(form.name+'_'+'_items['+curr_type_id+'_commentaire]');

    if (type == "commentaire") {
      //La case doit être cochée si un commentaire est présent
      checkbox.checked = $V(commentaire) ? 'checked' : '';
    }
    else {
      var div_comm = commentaire.up('div');
      if (!checkbox.checked) {
        div_comm.hide();
        $V(commentaire, '');
      }
      else {
        div_comm.show();
      }
    }
  },
  codeRouge: function(element) {
    if (element.checked) {
      if (confirm($T('CDailyCheckList-confirm-code_red'))) {
        EditDailyCheck.checkItemCodeRouge();
        $('checkList-container').select("div#check_list_avant_indu_cesar_ input[type='radio'], div#check_list_cesarienne_avant_ input[type='radio'], div#check_list_cesarienne_apres_ input[type='radio']").each(function(radio){
          radio.checked = false;
        });
      }
      else {
        element.checked = '';
      }
    }
    else {
      EditDailyCheck.checkItemCodeRouge();
    }
  },
  /**
   * Nous cochons la case à cocher "Code rouge" de la checklist HAS césariennne puis déclenchons la fonction les actions associées
   */
  checkedCodeRouge: function() {
    getForm('code_red_cesarienne').code_red.checked = 'checked';
    EditDailyCheck.checkItemCodeRouge();
  },
  /**
   * Selon l'état de la case à cocher "Code rouge" de la checklist HAS césariennne
   *    coché: Nous vidons l'ensemble des réponses en cours (formulaire non signé) et mettons en surbrillance les réponses urgentes
   *    décoché: Nous enlevons la surbrillance et rétablissons les réponses comme si le formulaire n'avait pas été rempli ou vidé
   */
  checkItemCodeRouge: function() {
    var element = $('code_red_cesarienne_code_red');
    var code_red = element.checked ? 1 : 0;
    var color = code_red ? "rgba(255,0,0,0.2)" : "#FFF";
    $$('.red_code').each(function(t){
      t.up('tr').style.backgroundColor = color;
      $V(t.form.code_red, code_red);
    });
  }
}
