{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}


<script>
  Main.add( function () {
    var form = getForm("editMultiplePersonnel");
    var url = new Url("personnel", "httpreq_do_personnels_autocomplete");
    url.autoComplete(form._view, form._view.id+'_autocomplete', {

      dropdown: true,
      minChars: 3,
      updateElement : function(element) {
        var table = $('selected_users');
        if (table.empty()) {
          $('users_empty_row').hide();
        }
        var user_id = element.id.split('-')[1];

        if ($$('tbody#selected_users input[data-user_id="' + user_id + '"]').length == 0) {
          var row = DOM.tr();
          row.insert(DOM.td({class: 'narrow'}, DOM.input({type: 'checkbox', name: 'user_id[]', 'data-user_id': user_id, checked: true})));
          row.insert(DOM.td({}, element.select(".view")[0].innerHTML.stripTags()));
          table.insert(row);
        }
      }
    });

    url = new Url('mediusers', 'ajax_functions_autocomplete');
    url.addParam('edit', '1');
    url.addParam('input_field', 'function_view');
    url.autoComplete(form.function_view, null, {
      minChars:           0,
      method:             'get',
      select:             'function_view',
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        var function_id = selected.get('id');
        var table = $('selected_functions');
        if (table.empty()) {
          $('functions_empty_row').hide();
        }

        if ($$('tbody#selected_functions input[data-function_id="' + function_id + '"]').length == 0) {
          var row = DOM.tr();
          var checkbox = DOM.input({type: 'checkbox', 'data-function_id': function_id, checked: true});
          checkbox.on('click', toggleUsersFunction.curry(checkbox));
          row.insert(DOM.td({class: 'narrow'}, checkbox));
          row.insert(DOM.td({}, selected.select(".view")[0].innerHTML.stripTags()));
          table.insert(row);
          getUsersFromFunction(function_id);
        }
      }
    });
  });

  getUsersFromFunction = function(function_id) {
    var url = new Url('mediusers', 'ajax_get_users_from_function');
    url.addParam('function_id', function_id);
    url.requestJSON(function(data) {
      if (data) {
        data.each(function(user) {
          var table = $('selected_users');
          if (table.empty()) {
            $('users_empty_row').hide();
          }

          if ($$('tbody#selected_users input[data-user_id="' + user.id + '"]').length == 0) {
            var row = DOM.tr();
            row.insert(DOM.td({class: 'narrow'}, DOM.input({
              type:               'checkbox',
              'data-user_id':     user.id,
              'data-function_id': function_id,
              checked:            true
            })));
            row.insert(DOM.td({}, user.view));
            table.insert(row);
          }
          else {
            $$('tbody#selected_users input[data-user_id="' + user.id + '"]')[0].setAttribute('data-function_id', function_id);
          }
        });
      }
    });
  };

  toggleCheckboxes = function(checkbox) {
    var table_id = 'selected_' + checkbox.get('objects');
    var checkboxes = $$('tbody#' + table_id + ' input[type="checkbox"]');
    checkboxes.each(function(cb) {
      cb.checked = checkbox.checked;
      if (checkbox.get('objects') == 'functions') {
        toggleUsersFunction(cb);
      }
    });
  };

  toggleUsersFunction = function(checkbox) {
    var checkboxes = $$('tbody#selected_users input[data-function_id="' + checkbox.get('function_id') + '"]');
    checkboxes.each(function(cb) {
      cb.checked = checkbox.checked;
    });
  };
</script>

<form name="editMultiplePersonnel" method="post" onsubmit="return Personnel.storeMultiple(this);">
  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$personnel}}

    <tr>
      <th>{{mb_label object=$personnel field="user_id"}}</th>
      <td>
        <input type="hidden" name="object_class" value="CMediusers" />
        <input size="30" name="_view" value="{{$personnel->_ref_user}}" {{if $personnel->user_id }}readonly="readonly"{{/if}}/>
        <div id="editFrm-{{$personnel->user_id}}__view_autocomplete" style="display: none; width: 300px;" class="autocomplete"></div>
      </td>
    </tr>

    <tr>
      <th><label for="function_view">{{tr}}CFunctions{{/tr}}</label></th>
      <td>
        <input size="30" name="function_view" value=""/>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$personnel field="emplacement"}}</th>
      <td>
        <select name="emplacement[]" multiple="true">
          <option value="">&mdash; {{tr}}Select{{/tr}}</option>
          {{assign var=types value='Ox\Mediboard\Personnel\CPersonnel'|static:'_types'}}
          {{foreach from=$types item=_type}}
            <option value="{{$_type}}">
              {{tr}}CPersonnel.emplacement.{{$_type}}{{/tr}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$personnel field="actif"}}</th>
      <td>{{mb_field object=$personnel field="actif"}}</td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        <button type="button" class="submit" name="btnFuseAction" onclick="this.form.onsubmit();">{{tr}}Create{{/tr}}</button>
      </td>
    </tr>
  </table>
  <div>
    <div style="width: 49%; display: inline-block;">
      <table class="tbl">
        <tr>
          <th class="narrow">
            <input type="checkbox" name="_toggle_users" data-objects="users" checked="checked" onclick="toggleCheckboxes(this);">
          </th>
          <th>
            Utilisateurs sélectionnés
          </th>
        </tr>
        <tbody id="selected_users">
        </tbody>
        <tr id="users_empty_row">
          <td class="empty" colspan="2">
            Aucun utilisateur sélectionné
          </td>
        </tr>
      </table>
    </div>
    <div style="width: 49%; display: inline-block; vertical-align: top;">
      <table class="tbl">
        <tr>
          <th class="narrow">
            <input type="checkbox" name="_toggle_functions" data-objects="functions" checked="checked" onclick="toggleCheckboxes(this);">
          </th>
          <th>
            Fonctions sélectionnées
          </th>
        </tr>
        <tbody id="selected_functions">
        </tbody>
        <tr id="functions_empty_row">
          <td class="empty" colspan="2">
            Aucune fonction sélectionnée
          </td>
        </tr>
      </table>
    </div>
  </div>
</form>
