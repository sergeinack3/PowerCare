{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=admin     script=view_access_token}}
{{mb_script module=mediusers script=CMediusers}}

<script>

  Main.add(function () {
    var form = getForm('search-token');

    // Autocomplete des actions (dépend du module sélectionné)
    var url = new Url("system", "ajax_seek_autocomplete");
    url.addParam("object_class", "CModuleAction");
    url.addParam("view_field", "action");
    url.addParam("input_field", "filter_action");
    url.autoComplete(form.elements.filter_action, null, {
      minChars:           2,
      method:             "get",
      dropdown:           true,
      callback:           function (input, queryString) {
        var form = getForm('search-token');
        var module = $V(form.elements.filter_module);

        if (module) {
          return queryString + "&where[module]=" + module;
        }

        return queryString;
      },
      afterUpdateElement: function (field, selected) {
        $V(form.elements.filter_action, selected.select(".view")[0].getText(), false);
        $V(form.elements.module_action_id, selected.get('id'));
        form.onsubmit();
      }
    });

    form.onsubmit();
  });

  function checkModule(input) {
    var form = input.form;

    ($V(input)) ? form.elements.filter_action.disabled = '' : form.elements.filter_action.disabled = '1';
  }

  doExportTokens = function () {
    var form = getForm('search-token');
    var url = new Url('admin', 'ajax_list_tokens', 'raw');
    url.addFormData(form);
    url.addParam('export', 1);
    url.pop();
  }
</script>

<form name="search-token" method="get" onsubmit="return onSubmitFormAjax(this, null, 'token-list');">
  <input type="hidden" name="m" value="admin" />
  <input type="hidden" name="a" value="ajax_list_tokens" />
  <input type="hidden" name="start" value="0" />

  {{mb_field object=$token field=module_action_id hidden=true}}

  <table class="main form">
    <col style="width: 10%;" />

    <tr>
      <th>{{mb_label object=$token field=hash}}</th>
      <td>{{mb_field object=$token field=hash canNull=true prop='str'}}</td>

      <th>
        {{tr}}common-Validity date{{/tr}}
      </th>

      <td class="narrow">
        {{mb_field object=$token field=_min_validity_date form='search-token' register=true}}
        &raquo;
        {{mb_field object=$token field=_max_validity_date form='search-token' register=true}}
      </td>

      <th>{{tr}}CModule{{/tr}}</th>

      <td>
        <select name="filter_module" style="width: 20em;"
                onchange="$V(this.form.elements.filter_action, '', false);
                  $V(this.form.elements.module_action_id, '', false); checkModule(this);">
          <option value="">&mdash; {{tr}}CModule.all{{/tr}}</option>

          {{foreach from=$modules item=_module}}
            <option value="{{$_module->mod_name}}">
              {{$_module}}
            </option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$token field=user_id}}</th>
      <td>
        <script>
          Main.add(CMediusers.standardAutocomplete.curry('search-token', 'user_id', '_view'));
        </script>
        <input type="hidden" name="user_id" />
        <input type="text" name="_view" class="autocomplete" placeholder="&mdash; {{tr}}All{{/tr}}" />
        <button type="button" class="cancel notext" onclick="$V(this.form.user_id, ''); $V(this.form._view, '');"></button>
      </td>


      <th>
        {{tr}}common-Usage date{{/tr}}
      </th>

      <td class="narrow">
        {{mb_field object=$token field=_min_usage_date form='search-token' register=true}}
        &raquo;
        {{mb_field object=$token field=_max_usage_date form='search-token' register=true}}
      </td>

      <th>{{tr}}Action{{/tr}}</th>
      <td>
        <input type="text" class="autocomplete" name="filter_action" value="" size="30" disabled
               placeholder="{{tr}}CModuleAction-All actions{{/tr}}" />

        <button type="button" class="erase notext compact"
                onclick="$V(this.form.elements.filter_action, ''); $V(this.form.elements.module_action_id, '');">
          {{tr}}Reset{{/tr}}
        </button>
      </td>
    </tr>

    <tr>
      <th>{{tr}}CViewAccessToken-active{{/tr}}</th>
      <td>
        <label>{{tr}}All{{/tr}} <input name="actif" value="" type="radio" onchange="$V(this.form.start, 0, false)"
                                       {{if !$actif && $actif != "0"}}checked{{/if}}/></label>
        <label>Actifs <input name="actif" value="1" type="radio" onchange="$V(this.form.start, 0, false)"
                             {{if $actif === "1"}}checked{{/if}}/></label>
        <label>Inactifs <input name="actif" value="0" type="radio" onchange="$V(this.form.start, 0, false)"
                               {{if $actif === "0"}}checked{{/if}}/></label>
      </td>

      <th>{{tr}}CViewAccessToken-purgeable{{/tr}}</th>
      <td>
        <label>{{tr}}All{{/tr}} <input name="purgeable" value="" type="radio" onchange="$V(this.form.start, 0, false)" checked/></label>
        <label>Oui <input name="purgeable" value="1" type="radio" onchange="$V(this.form.start, 0, false)"/></label>
        <label>Non<input name="purgeable" value="0" type="radio" onchange="$V(this.form.start, 0, false)"/></label>
      </td>

      <th>{{tr}}CViewAccessToken-restricted{{/tr}}</th>
      <td>
        <label>{{tr}}All{{/tr}} <input name="restricted" value="" type="radio" onchange="$V(this.form.start, 0, false)" checked/></label>
        <label>Oui <input name="restricted" value="1" type="radio" onchange="$V(this.form.start, 0, false)"/></label>
        <label>Non<input name="restricted" value="0" type="radio" onchange="$V(this.form.start, 0, false)"/></label>
      </td>
    </tr>

    <tr>
      <td>
        <button type="button" class="new" onclick="ViewAccessToken.edit();">
          {{tr}}CViewAccessToken-title-create{{/tr}}
        </button>
      </td>

      <td colspan="4" class="button">
        <button type="submit" class="search">{{tr}}Search{{/tr}}</button>
      </td>

      <td>
        <button class="fas fa-external-link-alt" type="button" onclick="doExportTokens();">
          {{tr}}CViewAccessToken-action-Export{{/tr}}
        </button>
      </td>
    </tr>
  </table>
</form>



<div id="token-list" class="me-padding-0"></div>