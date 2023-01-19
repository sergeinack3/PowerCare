/**
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

ObjectSelector = {
  sForm       : null,
  sId         : null,
  sView       : null,
  sClass      : null,
  onlyclass   : null,
  replacevalue: true,

  options : {
    width : 600,
    height: 500
  },
   
  pop: function() {
    var oForm = getForm(this.sForm);
    var url = new Url("system", "object_selector");
    url.addParam("onlyclass", this.onlyclass);
    url.addParam("selClass", oForm[this.sClass].value);
    url.addParam("replacevalue", this.replacevalue);
    url.popup(this.options.width, this.options.height, "Object Selector");
  },
  
  set: function(oObject) {
    var oForm = getForm(this.sForm);
    
    if (oForm[this.sView]) {
      $V(oForm[this.sView], oObject.view);
    }
    
    $V(oForm[this.sClass], oObject.objClass);

    if (this.replacevalue !== 'false') {
      $V(oForm[this.sId], oObject.id);
    }
    else {
      var oValue = (oForm[this.sId].value) ? oForm[this.sId].value + ',' + oObject.id : oObject.id;
      $V(oForm[this.sId], oValue);
    }

  }
};