/**
 * @package Mediboard\Ucum
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Ucum = {
    autoFill: function (conf) {
        $$('.ucumField').each(function (el) {
            new Def.Autocompleter.Search($(el), conf,
                {tableFormat: true, valueCols: [0], colHeaders: ['Code', 'Name']});
        });
    },
    updateConversion: function (conf) {
        var oForm = getForm("ucumForm");
        var url = new Url("ucum", "conversion");
        if (oForm.quantity || oForm.from || oForm.to) {
            url.addParam("quantity", oForm.quantity.value);
            url.addParam("from", oForm.from.value);
            url.addParam("to", oForm.to.value);
        }
        url.requestUpdate('conversion', {
            onComplete: Ucum.autoFill.curry(conf)
        });
    },
    updateValid: function (conf) {
        var oForm = getForm("ucumForm");
        var url = new Url("ucum", "validation");
        if (oForm.isValid) {
            url.addParam("unit", oForm.isValid.value);
        }
        url.requestUpdate('validation', {
            onComplete: Ucum.autoFill.curry(conf)
        });
    },
    updateToBase: function (conf) {
        var oForm = getForm("ucumForm");
        var url = new Url("ucum", "toBase");
        if (oForm.toBaseUnit) {
            url.addParam("units", oForm.toBaseUnit.value);
        }
        url.requestUpdate('toBase', {
            onComplete: Ucum.autoFill.curry(conf)
        });
    },
    updateAll: function () {
        Ucum.updateConversion();
        Ucum.updateValid();
        Ucum.updateToBase();
        return false;
    }
};
