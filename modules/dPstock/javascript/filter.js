/**
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

/** A filter function, useful */
var Filter = Class.create({
  initialize: function (sForm, sModule, sAction, sList, aFields, sHiddenColumn) {
    this.sForm = sForm;
    this.sModule = sModule;
    this.sAction = sAction;
    this.sList = sList;
    this.aFields = aFields;
    this.sHiddenColumn = sHiddenColumn;
    this.selected = 0;

    var oForm = this.getForm();
    this.aFields.each(function (f) {
      var oElement = $(oForm.elements[f]);
      if (oElement && oElement.observe) {
        oElement.observe('change', this.resetRange.bindAsEventListener(this));
      }
    }, this);
  },

  getForm: function () {
    return getForm(this.sForm);
  },

  submit: function (fieldToSelect) {
    var oForm = this.getForm();

    var makeRanges = function (total, step) {
      var ranges = [], i = 0;
      while (total > 0) {
        ranges.push(i * step + ',' + step);
        total -= step;
        i++;
      }
      return ranges;
    };

    var makeRangeSelector = function () {
      var form = this.getForm();

      this.sList.each(function (list) {
        var count = $(list + '-total-count');
        if (count) {
          count = parseInt(count.innerHTML);
        }

        var field = form.limit;
        var rangeSel = new Element('div', {className: 'pagination'});

        if (count > 30) {
          var total = count;
          var r = makeRanges(total, 30);

          r.each(function (e, k) {
            var a = new Element('a', {href: '#1', className: 'page', onclick: "return false"})
              .update(k + 1)
              .observe('click', function () {
                $V(field, e);
                form.onsubmit();
                this.selected = k;
              }.bind(this));

            if (k == this.selected) {
              a.addClassName('active');
            }
            rangeSel.insert(a);
          }, this);
        }
        $(list).insert(rangeSel);
      }, this);
    }.bind(this);

    if (!Object.isArray(this.sList)) {
      this.sList = [this.sList];
    }
    this.sList.each(function (list) {
      var url = new Url(this.sModule, this.sAction);

      this.aFields.each(function (f) {
        if (oForm[f]) {
          url.addParam(f, $V(oForm[f]));
        }
      });

      if (fieldToSelect) {
        var oField = oForm.elements[fieldToSelect];

        if (oField) {
          oField.focus();
          oField.select();
        }
      }

      if (this.sHiddenColumn) {
        url.addParam("hidden_column", this.sHiddenColumn);
      }

      url.requestUpdate(list, {onComplete: makeRangeSelector});
    }, this);

    return false;
  },

  empty: function (fields) {
    var oForm = this.getForm();
    if (!fields) {
      this.aFields.each(function (f) {
        if (oForm[f]) {
          oForm[f].value = '';
          oForm[f].selectedIndex = 0;
        }
      });
    } else if (typeof fields == "string") {
      if (oForm[fields]) {
        oForm[fields].value = '';
        oForm[fields].selectedIndex = 0;
      }
    } else {
      fields.each(function (f) {
        if (oForm[f]) {
          oForm[f].value = '';
          oForm[f].selectedIndex = 0;
        }
      });
    }
    this.submit();
  },

  resetRange: function () {
    this.selected = 0;
    $V(this.getForm().limit, '');
  }
});