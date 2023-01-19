{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  {{if $owner_id && $salutation->object_class === "CMedecin"}}
    Medecin.set = function(id, view) {
      var form = getForm('edit_salutation');

      $V(form.object_id, id);
      $('salutation_object_view').update(view);
    };
  {{/if}}

  Main.add(function () {
    var form = getForm('edit_salutation');

    var element_owner = form.elements._view_owner;
    var url = new Url("mediusers", "ajax_users_autocomplete");
    url.addParam("object_class", "CMediusers");
    url.addParam("edit", "1");
    url.addParam("praticiens", "1");

    url.addParam("input_field", element_owner.name);
    url.autoComplete(element_owner, null, {
      minChars:           2,
      method:             'get',
      select:             'view',
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        var id = selected.get("id");
        $V(form.elements.owner_id, id);
        if ($V(element_owner) == "") {
          $V(element_owner, selected.down('.view').innerHTML);
        }
      }
    });
  });
</script>

<form name="edit_salutation" method="post" onsubmit="return Salutation.submitSalutation(this);">
  {{mb_key object=$salutation}}
  {{mb_class object=$salutation}}
  <input type="hidden" name="del" value="" />

  {{mb_field object=$salutation field=owner_id hidden=true}}
  {{mb_field object=$salutation field=object_class hidden=true}}
  {{mb_field object=$salutation field=object_id hidden=true}}

  <table class="main form">
    <col class="narrow" />

    <tr>
      <th>{{mb_label class=CSalutation field=owner_id}}</th>
      <td>
        <input type="text" name="_view_owner" class="autocomplete" placeholder=" &mdash; {{tr}}CMediusers.select{{/tr}}" size="30"
               value="{{if $salutation->owner_id}}{{$salutation->_ref_owner}}{{/if}}" />

        <button type="button" class="user notext compact"
                onclick="$V(this.form.elements.owner_id, User.id);
                  $V(this.form.elements._view_owner, User.view);">
        </button>

        <button type="button" class="erase notext compact"
                onclick="$V(this.form.elements.owner_id, ''); $V(this.form.elements._view_owner, '');">
        </button>
      </td>
    </tr>

    <tr>
      <th>{{mb_label class=CSalutation field=object_id}}</th>
      <td>
        <span id="salutation_object_view">
          {{mb_value object=$salutation field=object_id tooltip=true}}
        </span>

        {{if $owner_id && $salutation->object_class === "CMedecin"}}
          <button type="button" class="search" onclick="Medecin.edit(this.form);">{{tr}}Change{{/tr}}</button>
        {{/if}}
      </td>
    </tr>

    <tr>
      <th>{{mb_label class=CSalutation field=starting_formula}}</th>
      <td>{{mb_field object=$salutation field=starting_formula style='width: 95%; box-sizing: border-box;'}}</td>
    </tr>

    <tr>
      <th>{{mb_label class=CSalutation field=closing_formula}}</th>
      <td>{{mb_field object=$salutation field=closing_formula style='width: 95%; box-sizing: border-box;'}}</td>
    </tr>

    <tr>
      <th>{{mb_label class=CSalutation field=tutoiement typeEnum=checkbox}}</th>
      <td>{{mb_field object=$salutation field=tutoiement typeEnum=checkbox}}</td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if !$salutation->_id}}
          <button type="submit" class="save">{{tr}}Save{{/tr}}</button>
        {{else}}
          <button type="submit" class="save">{{tr}}Edit{{/tr}}</button>
          <button type="button" class="trash" onclick="confirmDeletion(this.form,{ajax:true}, {onComplete: Control.Modal.close})">
            {{tr}}Delete{{/tr}}
          </button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>