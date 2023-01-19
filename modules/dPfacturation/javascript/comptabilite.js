/**
 * @package Mediboard\dPfacturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

Comptabilite = {
  prepareUserField : function(form, field, compta, hide_onload) {
    var formField = form[field];
    new Url("mediusers", "ajax_users_autocomplete")
      .addParam("edit", '1')
      .addParam("compta", compta)
      .addParam("input_field", field)
      .autoComplete(
        formField,
        null,
        {
          minChars: 0,
          method: "get",
          select: "view",
          dropdown: true,
          afterUpdateElement: function(field, selected) {
            if ($V(formField) == "") {
              $V(formField, selected.down('.view').innerHTML);
            }
            var id = selected.getAttribute("id").split("-")[2];
            $V(form.chir, id);
          }
        }
      );
  },
  toggleUserSelector: function(form) {
    form.chir_view.up('#chir_view_container').toggle();
    form.all_chir_view.up('#all_chir_view_container').toggle();
  },

  v11 : {
    impression: function(){
      $('form_upload').hide();
      $('button_print').hide();
      window.print();
      $('form_upload').show();
      $('button_print').show();
    },
    export: function(element) {
      var a = DOM.a(
        {
          download: $T("File") + ".csv",
          href: 'data:text/csv;charset=UTF-8;filename:Export,' + encodeURIComponent(element.get('csv')),
          style: 'display: none'
        }
      );
      document.body.appendChild(a);
      a.click();
      a.remove();
    }
  },
  camt054 : {
    impression: function(){
      $('form_upload').hide();
      $('button_print').hide();
      window.print();
      $('form_upload').show();
      $('button_print').show();
    }
  }
};