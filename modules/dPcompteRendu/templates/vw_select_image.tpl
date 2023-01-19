{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  refreshListImages = function(context_guid) {
    new Url("compteRendu", "ajax_list_images")
      .addParam("context_guid", context_guid)
      .requestUpdate("area_list_images");
  };

  addImages = function() {
    var form = getForm("changeContext");
    var context_guid = $V(form.context_guid);
    uploadFile(context_guid, null, null, null, refreshListImages.curry(context_guid));
  };

  Main.add(function() {
    var form = getForm("changeContext");
    new Url("mediusers", "ajax_users_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "user_id_view")
      .autoComplete(form.user_id_view, "users_autocomplete", {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.context, "", false);
        $V(form.context_type, "user");
        $V(form.function_id_view, "");
        $V(form.group_id_view, "");
        $V(form.context_guid, "CMediusers-" + id);
      }
    });

    new Url("mediusers", "ajax_functions_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "function_id_view")
      .addParam("view_field", "text")
      .autoComplete(form.function_id_view, "functions_autocomplete", {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.context, "", false);
        $V(form.context_type, "function");
        $V(form.user_id_view, "");
        $V(form.group_id_view, "");
        $V(form.context_guid, "CFunctions-" + id);
      }
    });

    new Url("etablissement", "ajax_groups_autocomplete")
      .addParam("edit", "1")
      .addParam("input_field", "group_id_view")
      .addParam("view_field", "text")
      .autoComplete(form.group_id_view, "groups_autocomplete", {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.context, "", false);
        $V(form.context_type, "group");
        $V(form.user_id_view, "");
        $V(form.function_id_view, "");
        $V(form.context_guid, "CGroups-" + id);
      }
    });

    refreshListImages("{{$context->_guid}}");
  });
</script>

<form name="changeContext" method="get">
  <input type="hidden" name="context_guid" value="{{$context->_guid}}" onchange="refreshListImages(this.value);" />

  <fieldset>
    <legend>{{tr}}mod-dPpatients-tab-ajax_context_doc{{/tr}}</legend>

    <table class="main" style="text-align: center;">
      <tr>
        <th style="width: 25%;">
          {{if $patient}}

          <input type="radio" name="context_type" value="object"
            {{if !$context|instanceof:'Ox\Mediboard\Mediusers\CMediusers' && !$context|instanceof:'Ox\Mediboard\Mediusers\CFunctions' && !$context|instanceof:'Ox\Mediboard\Etablissement\CGroups'}}checked{{/if}} />
          <select name="context" style="width: 150px;"
                  onchange="if (!this.value) { return; } $V(this.form.context_type, 'object'); $V(this.form.user_id_view, ''); $V(this.form.function_id_view, '');
            $V(this.form.group_id_view, ''); $V(this.form.context_guid, this.value);">
            <option value="">&mdash; {{tr}}CCompteRendu-object_class{{/tr}}</option>
            <option value="{{$patient->_guid}}" {{if $patient->_guid == $context->_guid}}selected{{/if}}>
              {{tr}}CPatient{{/tr}} {{$patient}}
            </option>
            {{foreach from=$patient->_ref_sejours item=_sejour}}
              <option value="{{$_sejour->_guid}}" {{if $_sejour->_guid == $context->_guid}}selected{{/if}}>
                {{tr}}CSejour{{/tr}} {{$_sejour->_shortview}}
              </option>
              {{foreach from=$_sejour->_ref_consultations item=_consult}}
                <option value="{{$_consult->_guid}}" style="margin-left: 20px;" {{if $_consult->_guid == $context->_guid}}selected{{/if}}>
                  {{assign var=date_consult value=$_consult->_datetime|date_format:$conf.date}}
                  {{tr}}CConsultation{{/tr}} {{tr var1=$date_consult}}common-the %s{{/tr}}
                </option>
              {{/foreach}}
              {{foreach from=$_sejour->_ref_operations item=_op}}
                <option value="{{$_op->_guid}}" style="margin-left: 20px;" {{if $_op->_guid == $context->_guid}}selected{{/if}}>
                  {{assign var=date_interv value=$_op->_datetime|date_format:$conf.date}}
                  {{tr}}COperation{{/tr}} {{tr var1=$date_interv}}common-the %s{{/tr}}
                </option>
              {{/foreach}}
            {{/foreach}}
          </select>
          {{else}}
            &mdash;
          {{/if}}
        </th>
        <th style="width: 25%;">
          <input type="radio" name="context_type" value="user" {{if $context|instanceof:'Ox\Mediboard\Mediusers\CMediusers'}}checked{{/if}} />
          <input type="text" name="user_id_view" {{if $context|instanceof:'Ox\Mediboard\Mediusers\CMediusers'}}value="{{$context}}"{{/if}}
                 style="width: 100px;" placeholder="&mdash; {{tr}}common-User{{/tr}}" />
          <div id="users_autocomplete" class="autocomplete" style="text-align: left; display: none;"></div>
        </th>
        <th style="width: 25%;">
          <input type="radio" name="context_type" value="function" {{if $context|instanceof:'Ox\Mediboard\Mediusers\CFunctions'}}checked{{/if}} />
          <input type="text" name="function_id_view" {{if $context|instanceof:'Ox\Mediboard\Mediusers\CFunctions'}}value="{{$context}}"{{/if}}
                 style="width: 100px;" placeholder="&mdash; {{tr}}Function{{/tr}}" />
          <div id="functions_autocomplete" class="autocomplete" style="text-align: left; display: none;"></div>
        </th>
        <th style="width: 25%;">
          <input type="radio" name="context_type" value="group" {{if $context|instanceof:'Ox\Mediboard\Etablissement\CGroups'}}checked{{/if}} />
          <input type="text" name="group_id_view" {{if $context|instanceof:'Ox\Mediboard\Etablissement\CGroups'}}value="{{$context}}"{{/if}}
                 style="width: 100px;" placeholder="&mdash; {{tr}}common-Establishment{{/tr}}" />
          <div id="groups_autocomplete" class="autocomplete" style="text-align: left; display: none;"></div>
        </th>
      </tr>
    </table>
  </fieldset>
  <br />
  <button type="button" class="add" onclick="addImages();">{{tr}}CFile-add_picture{{/tr}}</button>
</form>

<div id="area_list_images"></div>