/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

// $Id: $
Consultation = window.Consultation || {
  edit: function(consult_id, fragment) {
    new Url().
      setModuleTab('cabinet', 'edit_consultation').
      addParam('selConsult', consult_id).
      setFragment(fragment).
      redirectOpener();
  },

  plan: function(consult_id) {
    new Url().
      setModuleTab('cabinet', 'edit_planning').
      addParam('consultation_id', consult_id).
      redirectOpener();
  },

  macroStats: function(button) { 
    var form = button.form;
    new Url('cabinet', 'user_stats')
      .addElement(form.period)
      .addElement(form.date)
      .addElement(form.type)
      .addElement(form.consult_no_sejour)
      .addElement(form.consult_sejour_consult)
      .addElement(form.consult_sejour_urg)
      .addElement(form.consult_sejour_ext)
      .addElement(form.consult_sejour_autre)
      .requestModal(-100, -100);
  },
  
  checkParams: function() {
    new Url('cabinet', 'check_params').requestModal(950);
  },

  importPlanning: function(prat_id) {
    new Url('cabinet', 'vw_import_planning')
      .addParam("prat_id", prat_id)
      .pop(600, 600);
  },

  importPlanningLite: function() {
    new Url('cabinet', 'vw_import_planning')
      .addParam("lite", 1)
      .pop(600, 600);
  },

  printExamen: function(consult_id) {
    new Url('cabinet', 'print_examen')
      .addParam('consult_id', consult_id)
      .popup(700, 500, 'printExamen');
  },

  createSejours: function() {
    new Url('cabinet', 'vw_create_sejours_for_consults')
      .modal();
  }
};
