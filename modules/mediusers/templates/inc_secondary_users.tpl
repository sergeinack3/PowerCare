{{*
 * @package Mediboard\mediusers
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  emptyFunction = function() {
    var form = getForm('searchSecondaryUsers');
    $V(form.elements['function_id'], '');
    $V(form.elements['function_view'], '');
  };

  filterSecondaryUsers = function(form) {
    var url = new Url('mediusers', 'ajax_secondary_users');
    url.addParam('main_user_id', '{{$main_user->_id}}');
    url.addParam('action', 'search');
    url.addParam('filter', $V(form.elements['filter']));
    url.addParam('function_id', $V(form.elements['function_id']));
    url.requestUpdate('secondary_user_action');
    return false;
  };

  createSecondaryUser = function() {
    var url = new Url('mediusers', 'ajax_secondary_users');
    url.addParam('main_user_id', '{{$main_user->_id}}');
    url.addParam('action', 'create');
    url.requestUpdate('secondary_user_action');
    return false;
  };

  Main.add(function() {
    new Url('mediusers', 'ajax_functions_autocomplete')
    .addParam('edit', '1')
    .addParam('input_field', 'function_view')
    .addParam('view_field', 'text')
    .autoComplete(getForm('searchSecondaryUsers').elements['function_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        if ($V(field) == '') {
          $V(field, selected.down('.view').innerHTML);
        }

        $V(field.form.elements['function_id'], selected.getAttribute('id').split('-')[2]);
      }
    });

    filterSecondaryUsers(getForm('searchSecondaryUsers'));
  });
</script>

<form name="searchSecondaryUsers" method="get" action="?" onsubmit="return filterSecondaryUsers(this);">
  <table class="form">
    <tr>
      <th class="title" colspan="2"></th>
    </tr>
    <tr>
      <th>
        <label for="filter">Mots clés</label>
      </th>
      <td>
        <input type="text" name="filter" value="{{$main_user->_user_last_name}}">
      </td>
    </tr>
    <tr>
      <th>
        <label for="function_id">Fonction</label>
      </th>
      <td>
        <input type="hidden" name="function_id" value="{{$main_user->_ref_function->_id}}">
        <input type="text" name="function_view" value="{{$main_user->_ref_function}}">
        <button type="button" class="cancel notext" onclick="emptyFunction();">{{tr}}Empty{{/tr}}</button>
      </td>
    </tr>
    <tr>
      <td class="button" colspan="2">
        <button type="button" class="search" onclick="this.form.onsubmit();">{{tr}}Filter{{/tr}}</button>
        <button type="button" class="new" onclick="createSecondaryUser();">{{tr}}Create{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<div id="secondary_user_action">
  
</div>