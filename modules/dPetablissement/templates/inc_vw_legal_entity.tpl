{{*
 * @package Mediboard\Etablissement
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    InseeFields.initCPVille("legal_entity", "zip_code", "city", null, null, "country");

    var form = getForm("legal_entity");
    var url = new Url("mediusers", "ajax_users_autocomplete");
    url.addParam("input_field", 'mediuser_view');
    url.autoComplete(form.mediuser_view, null, {
      minChars: 0,
      method: "get",
      select: "view",
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        if ($V(form.mediuser_view) == "") {
          $V(form.mediuser_view, selected.down('.view').innerHTML);
        }
        var id = selected.getAttribute("id").split("-")[2];
        $V(form.user_id, id);
      }
    });
  });
</script>


<form name="legal_entity" method="post" onsubmit="return checkForm(this);">
  {{mb_class object=$legal_entity}}
  {{mb_key   object=$legal_entity}}
  {{mb_field object=$legal_entity field=user_id hidden=1}}

  <table class="main form">
    <tr>
      <th>{{mb_label object=$legal_entity field=_name}}</th>
      <td>{{mb_field object=$legal_entity field=_name}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=code}}</th>
      <td>{{mb_field object=$legal_entity field=code}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=short_name}}</th>
      <td>{{mb_field object=$legal_entity field=short_name}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=description}}</th>
      <td>{{mb_field object=$legal_entity field=description}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=opening_reason}}</th>
      <td>{{mb_field object=$legal_entity field=opening_reason}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=opening_date}}</th>
      <td>{{mb_field object=$legal_entity field=opening_date form=legal_entity register=true}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=closing_reason}}</th>
      <td>{{mb_field object=$legal_entity field=closing_reason}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=closing_date}}</th>
      <td>{{mb_field object=$legal_entity field=closing_date form=legal_entity register=true}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=activation_date}}</th>
      <td>{{mb_field object=$legal_entity field=activation_date form=legal_entity register=true}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=inactivation_date}}</th>
      <td>{{mb_field object=$legal_entity field=inactivation_date form=legal_entity register=true}}</td>
    </tr>

    <tr>
      <th>{{tr}}CLegalEntity-mediuser view{{/tr}}</th>
      <td>
        <input type="text" name="mediuser_view" value="{{$legal_entity->_ref_user}}" />
        <button type="button" class="erase notext"
                onclick="$V(this.form.elements.user_id, ''); $V(this.form.elements.mediuser_view, '');"
                style="display: inline-block !important;"></button>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=finess}}</th>
      <td>{{mb_field object=$legal_entity field=finess}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=rmess}}</th>
      <td>{{mb_field object=$legal_entity field=rmess}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=address}}</th>
      <td>{{mb_field object=$legal_entity field=address}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=zip_code}}</th>
      <td>{{mb_field object=$legal_entity field=zip_code}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=city}}</th>
      <td>{{mb_field object=$legal_entity field=city}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=country}}</th>
      <td>{{mb_field object=$legal_entity field=country}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=insee}}</th>
      <td>{{mb_field object=$legal_entity field=insee}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=siren}}</th>
      <td>{{mb_field object=$legal_entity field=siren}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=nic}}</th>
      <td>{{mb_field object=$legal_entity field=nic}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$legal_entity field=legal_status_code}}</th>
      <td>
        <select name="legal_status_code" style="width:14em; vertical-align: top">
          <option value="">{{tr}}Choose{{/tr}}</option>
          {{foreach from=$legal_status item=_status}}
            {{assign var=code value=$_status->_id}}
            <option value="{{$_status->_id}}" {{if $legal_entity->legal_status_code == $code}}selected{{/if}}>{{$code}} - {{$_status->short_name}}</option>
          {{/foreach}}
        </select>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $legal_entity->_id}}
          <button class="modify" type="submit" name="modify">
            {{tr}}Save{{/tr}}
          </button>
          <button class="trash" type="button" name="delete" onclick="confirmDeletion(this.form, {typeName:'l\'entité juridique', objName: $V(this.form._name)})">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="new" type="submit" name="create">
            {{tr}}Create{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
