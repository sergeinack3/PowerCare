{{*
 * @package Mediboard\Forms
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=soins script=soins}}

<script>
  Main.add(function () {
    var form = getForm('search-mandatory-forms');
    Calendar.regField(form.elements.date);

    form.onsubmit();
  });

  savePref = function (form) {
    var formPref = getForm('editPrefServiceSoins');
    var formService = getForm('search-mandatory-forms');
    var service_id = $V(form.default_service_id);

    var default_service_id_elt = formPref.elements['pref[default_services_id]'];
    var default_service_id = $V(default_service_id_elt).evalJSON();

    default_service_id.g{{$g}} = service_id;
    $V(default_service_id_elt, Object.toJSON(default_service_id));

    return onSubmitFormAjax(formPref, function () {
      Control.Modal.close();
      $V(formService.service_id, service_id);
    });
  }
</script>

<form name="search-mandatory-forms" method="get" onsubmit="return onSubmitFormAjax(this, null, 'mandatory-forms');">
  <input type="hidden" name="m" value="forms" />
  <input type="hidden" name="a" value="ajax_search_mandatory_forms" />

  <table class="main form">
    <col style="width: 10%;" />

    <tr>
      <th>{{tr}}common-Date{{/tr}}</th>

      <td>
        <input type="hidden" name="date" value="{{$date}}" />
      </td>

      <th>{{tr}}CService{{/tr}}</th>
      <td>
        {{if !'soins Sejour select_services_ids'|gconf}}
          <label for="service_id">
            <button type="button" class="search notext" title="Service par défaut"
                    onclick="Modal.open('select_default_service', { showClose: true, title: 'Service par défaut' })">Service</button>
          </label>
        {{/if}}

        {{if 'soins Sejour select_services_ids'|gconf}}
          <button type="button" onclick="Soins.selectServices('soins');" class="search">Services</button>
        {{else}}
          <select name="service_id" style="max-width: 135px;">
            <option value="">&mdash; Service</option>

            {{foreach from=$services item=curr_service}}
              <option value="{{$curr_service->_id}}" {{if $curr_service->_id == $service_id}}selected{{/if}}>
                {{$curr_service->nom}}
              </option>
            {{/foreach}}

            <option value="NP" {{if $service_id == 'NP'}}selected{{/if}}>Non placés</option>
          </select>
        {{/if}}

        {{mb_include module=soins template=vw_select_default_service}}
      </td>
    </tr>

    <tr>
      <td colspan="4" class="button">
        <button type="submit" class="search">{{tr}}common-action-Search{{/tr}}</button>
      </td>
    </tr>
  </table>
</form>

<form name="editPrefServiceSoins" method="post">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="dosql" value="do_preference_aed" />
  <input type="hidden" name="user_id" value="{{$app->user_id}}" />
  {{assign var=default_services_id value="{}"}}
  {{if isset($app->user_prefs.default_services_id|smarty:nodefaults)}}
    {{assign var=default_services_id value=$app->user_prefs.default_services_id}}
  {{/if}}
  <input type="hidden" name="pref[default_services_id]" value="{{$default_services_id|html_entity_decode}}" />
</form>

<div id="mandatory-forms"></div>