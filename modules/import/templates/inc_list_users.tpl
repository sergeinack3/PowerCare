{{*
 * @package Mediboard\Weda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=import script=import_mapping ajax=true}}
{{mb_default var=step value=50}}


{{mb_include module=system template=inc_pagination change_page="ImportMapping.changePage"
  total=$total current=$start step=$step change_page_arg=$change_page_arg|@json
}}

<table class="main tbl">
  <tr>
    <th>External ID</th>
    <th>Username</th>
    <th></th>
  </tr>

  {{foreach from=$user_list key=_id item=_infos}}
    {{assign var=_mb_object value=$_infos.mb_user}}

    <script>
      Main.add(function () {
        var form = getForm("bind-user-{{$_id}}");
        var element = form.elements._user_id_autocomplete_view;
        var url = new Url("system", "ajax_seek_autocomplete");

        url.addParam("object_class", "CMediusers");
        url.addParam("input_field", element.name);
        url.autoComplete(element, null, {
          minChars:           3,
          method:             "get",
          select:             "view",
          dropdown:           true,
          afterUpdateElement: function (field, selected) {
            var id = selected.getAttribute("id").split("-")[2];
            $V(form.user_id, id);

            if ($V(element) == "") {
              $V(element, selected.down('.view').innerHTML);
            }
          }
        });
      });
    </script>

    <tr>
      <td class="narrow">{{$_id}}</td>
      <td class="narrow">{{$_infos.username}}</td>
      <td>
        <form name="bind-user-{{$_id}}" method="post" onsubmit="return onSubmitFormAjax(this)">
          <input type="hidden" name="m" value="{{$mod_name}}"/>
          <input type="hidden" name="dosql" value="do_bind_user"/>
          <input type="hidden" name="campaign_id" value="{{$campaign->_id}}"/>
          <input type="hidden" name="ext_id" value="{{$_id}}"/>
          <input type="hidden" name="user_id" value="{{if $_mb_object}}{{$_mb_object->_id}}{{/if}}" onchange="this.form.onsubmit();"/>

          <input type="text" name="_user_id_autocomplete_view"
                 value="{{if $_mb_object}}{{$_mb_object}}{{/if}}" size="40" />

          <button type="button" class="down notext" onclick="ImportMapping.copieUser(this.form);">
            {{tr}}mod-import-Action-Copy to next{{/tr}}
          </button>
        </form>

      </td>
    </tr>

    {{foreachelse}}
    <tr>
      <td colspan="2" class="empty">
        {{tr}}Empty{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
