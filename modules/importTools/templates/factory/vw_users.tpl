{{*
 * @package Mediboard\ImportTools
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  createCallback = function(code, user_id, user_view) {
    var form = getForm("bind-praticien-"+code);
    $V(form._user_id_autocomplete_view, user_view);
  }
</script>

<table class="main tbl">
  <tr>
    <th class="narrow">Nb</th>
    <th class="narrow">Code</th>
    <th class="narrow">Nom</th>
    <th class="narrow">Prénom</th>
    <th class="narrow">Login</th>
    <th class="narrow">Type</th>
    <th class="narrow">Spé</th>
    <th style="text-align: left;">{{tr}}CUser{{/tr}}</th>
  </tr>

  {{foreach from=$users item=_data}}
    <script>
      Main.add(function () {
        var form = getForm("bind-praticien-{{$_data.ID|md5}}");
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
      <td class="narrow">{{$_data.count}}</td>
      <td class="narrow"><strong>{{$_data.ID}}</strong></td>
      <td class="narrow">{{$_data.lastname}}</td>
      <td class="narrow">{{$_data.firstname}}</td>
      <td class="narrow">{{$_data.username}}</td>
      <td class="narrow">{{$_data.type}}</td>
      <td class="narrow">{{$_data.specialty}}</td>

      <td>
        <form name="bind-praticien-{{$_data.ID|md5}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this);">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="dosql" value="do_bind_user_code" />
          <input type="hidden" name="user_class" value="{{$object->getUserClass()}}" />
          <input type="hidden" name="tag" value="{{$object->getImportTag()}}" />
          <input type="hidden" name="code" value="{{$_data.ID}}" />
          <input type="hidden" name="user_id" value="{{$_data.object->_id}}" onchange="this.form.onsubmit();" />

          <input type="text" name="_user_id_autocomplete_view" value="{{if $_data.object && $_data.object->_id}}{{$_data.object}}{{/if}}" size="40" />
        </form>

        OU

        <form name="create-praticien-{{$_data.ID|md5}}" method="post" action="?" onsubmit="return onSubmitFormAjax(this, null, this.down('.report'));">
          <input type="hidden" name="m" value="{{$m}}" />
          <input type="hidden" name="dosql" value="do_bind_user_code" />
          <input type="hidden" name="user_class" value="{{$object->getUserClass()}}" />
          <input type="hidden" name="tag" value="{{$object->getImportTag()}}" />
          <input type="hidden" name="code" value="{{$_data.ID}}" />
          <input type="hidden" name="create" value="1" />
          {{foreach from='Ox\Core\Import\CExternalDBImport'|static:_base_user item=_value key=_key}}
            <input type="hidden" name="data[{{$_key}}]" value="{{$_data.$_key}}" />
          {{/foreach}}
          <button type="submit" class="submit">{{tr}}Create{{/tr}}</button>
          <div class="report" style="display: inline-block;"></div>
        </form>
      </td>
    </tr>
  {{/foreach}}
</table>