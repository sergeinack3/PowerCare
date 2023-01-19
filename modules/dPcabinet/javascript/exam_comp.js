/**
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

var ExamComp = {
  del: function(form) {
    form.del.value = "1";
    ExamComp.submit(form);
  },
  
  toggle: function(form){
    form.fait.value = (form.fait.value == 1) ? 0 : 1;
    ExamComp.submit(form);
  },

  submit: function(form) {
    if (form.examen) {
      var examen = $V(form.examen);
      var realisation = $V(form.realisation);
    }
    
    onSubmitFormAjax(form, function() {
      ExamComp.refresh($V(form.consultation_id));
    });
    form.reset();
    if (form.examen) {
      form._hidden_examen.value = examen;
      form.realisation.value = realisation;
    }
  },
  
  refresh: function (consultation_id) {
    new Url("cabinet", "httpreq_vw_list_exam_comp")
      .addParam("selConsult", consultation_id)
      .requestUpdate('listExamComp', callbackExamComp);
  }
};
