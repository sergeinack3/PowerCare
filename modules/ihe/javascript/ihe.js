/**
 * @package Mediboard\Ihe
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

IHE = {
  addFunction : function (element) {
    var value = $V(element);
    if (!value) {
      return false;
    }

    var form = getForm("editiheConfig");
    var tokenfield = new TokenField(form.elements["ihe[RAD-3][function_ids]"]);

    if (tokenfield.contains(value)) {
      return false;
    }

    tokenfield.add(value);
    var text = element.options[element.options.selectedIndex].text;
    var color = element.options[element.options.selectedIndex].get("color");

    IHE.createTag(text, value, color);
    $V(element, "");
  },

  delFunction : function (element, id) {
    var form = getForm("editiheConfig");
    var tokenfield = new TokenField(form.elements["ihe[RAD-3][function_ids]"]);
    tokenfield.remove(id);
    Element.remove(element.up());
  },

  createTag : function (name, id, color) {
    var list = $("listFunctions");
    list.appendChild(DOM.li({className:"tag", style:"background-color: #"+color}, name,
      DOM.button({type:'button', className:"delete", onclick:'IHE.delFunction(this, '+id+')'})
    ));
  }
};