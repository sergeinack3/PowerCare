/**
 * @package Mediboard\Hprim21
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

PatHprimSelector = {
  sForm       : null,
  sId         : null,
  sPatient_id : null,
  sPatNom     : null,
  sPatPrenom  : null,
  options : {
    width : 750,
    height: 500
  },
  prepared : {
    id: null
  },
  pop: function() {
    var url = new Url();
    url.setModuleAction("hprim21", "pat_hprim_selector");
    if(this.sPatient_id) {
      url.addParam("patient_id", this.sPatient_id);
    } else {
      url.addParam("name", this.sPatNom);
      url.addParam("firstName", this.sPatPrenom);
    }
    url.popup(this.options.width, this.options.height, "PatientHprim");
  },
  
  set: function(id) {
    this.prepared.id = id;
    
    // Lancement de l'execution du set
    window.setTimeout( window.PatHprimSelector.doSet , 1);
  },
    
  doSet: function(){
    var oForm = document[PatHprimSelector.sForm];
    $V(oForm[PatHprimSelector.sId], PatHprimSelector.prepared.id);
  }
};
