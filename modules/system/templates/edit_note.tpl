{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add(function () {
    var form = getForm("Edit-{{$note->_guid}}");

    if (Preferences.notes_anonymous == "1") {
      var elt = form.notes_anonymous;
      elt.checked = true;
      elt.onclick();
    }

    window.usersTokenField = new TokenField(
      form.elements._share_ids, {
        onChange: function () {
        }.bind(form.elements._share_ids)
      }
    );

    var input_field = form.elements._user_autocomplete;
    var id_field = form.elements._share_ids;

    var url = new Url('mediusers', 'ajax_users_autocomplete');
    url.addParam('input_field', input_field.name);
    url.autoComplete(input_field, null, {
      minChars:           0,
      method:             'get',
      select:             'view',
      dropdown:           true,
      afterUpdateElement: function (field, selected) {
        if ($V(input_field) == '') {
          $V(input_field, selected.down('.view').innerHTML);
        }

        var id = selected.getAttribute('id').split('-')[2];
        var name = selected.down().down('span').getText();

        var to_insert = !window.usersTokenField.contains(id);
        window.usersTokenField.add(id);

        if (to_insert) {
          insertUserTag(id, name);
        }

        $V(input_field, '');
      }
    });
  });

  var user_id = {{$note->user_id}};
  var user_view = '';

  toggleAnonymous = function (state) {
    var oForm = getForm("Edit-{{$note->_guid}}");
    $V(oForm.user_id, state ? '' : user_id);
    $('note_author').toggle();
    $('no_author').toggle();

    $("Edit-{{$note->_guid}}_public_1").checked = 'checked';
    $("Edit-{{$note->_guid}}_public_1").onchange();

    $("Edit-{{$note->_guid}}_public_0").disabled = state ? 'disabled' : '';
  };

  toggleShare = function (input) {
    var form = input.form;

    if ($V(form.elements.note_id)) {
      return;
    }

    if ($V(input) === '1') {
      form.elements._user_autocomplete.disabled = 'disabled';
      $V(form.elements.share_ids, '');
      $('user_ids_tags').update();
    }
    else {
      form.elements._user_autocomplete.disabled = '';
    }
  };

  showNoteSharing = function () {
    Modal.open('note-sharing', {width: '400px', height: '200px', showClose: true});
  };

  insertUserTag = function (id, name) {
    var tag = $('CTag-' + id);

    if (!tag) {
      var btn = DOM.button({
        'type':      'button',
        'className': 'delete',
        'style':     "display: inline-block !important",
        'onclick':   "window.usersTokenField.remove($(this).up('li').get('tag_item_id')); this.up().remove();"
      });

      var li = DOM.li({
        'data-tag_item_id': id,
        'id':               'CTag-' + id,
        'className':        'tag'
      }, name, btn);

      $('user_ids_tags').insert(li);
    }
  }
</script>

<form name="Edit-{{$note->_guid}}" method="post" onsubmit="return Note.submit(this);">
  {{mb_class object=$note}}
  {{mb_key   object=$note}}
  <input type="hidden" name="m" value="system" />
  <input type="hidden" name="dosql" value="do_note_aed" />
  <input type="hidden" name="guid" value="{{$note->_guid}}"/>

  {{mb_field object=$note field=object_id    hidden=true}}
  {{mb_field object=$note field=object_class hidden=true}}

  <input type="hidden" name="_share_ids" value="" />

  <table class="main form">
    <tr>
      {{if $note->_id}}
        <th class="title modify" colspan="2">
          {{tr}}{{$note->_class}}-title-modify{{/tr}}
          <br />
          '{{$note->_ref_object}}'
        </th>
      {{else}}
        <th class="title me-th-new" colspan="2">
          {{tr}}{{$note->_class}}-title-create{{/tr}}
          <br />
          '{{$note->_ref_object}}'
        </th>
      {{/if}}
    </tr>

    <tr>
      <th style="width: 135px;" rowspan="2">{{mb_label object=$note field="user_id"}}</th>
      <td>
        <span id="note_author">{{$note->_ref_user}} &mdash; {{$note->_ref_user->_ref_function}}</span>
        <span id="no_author" style="display: none;">{{tr}}CNote.no_author{{/tr}}</span>
        {{mb_field object=$note field="user_id" hidden=1}}
      </td>
    </tr>

    <tr>
      <td>
        <label>
          <input type="checkbox" name="notes_anonymous" onclick="toggleAnonymous(this.checked);" />
          {{tr}}CNote-no_user{{/tr}}
        </label>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$note field=date}}</th>
      <td>{{mb_field object=$note field=date form="Edit-`$note->_guid`" register=true}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$note field=public}}</th>
      <td>{{mb_field object=$note field=public onchange='toggleShare(this);'}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$note field=degre}}</th>
      <td>{{mb_field object=$note field=degre typeEnum='radio'}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$note field=libelle}}</th>
      <td>{{mb_field object=$note field=libelle}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$note field=text}}</th>
      <td>{{mb_field object=$note field=text}}</td>
    </tr>

    <tr>
      <th>{{tr}}common-Sharing{{/tr}}</th>

      <td>
        <input type="text" name="_user_autocomplete" value="" {{if !$note->public}} disabled{{/if}} />

        <ul id="user_ids_tags" class="tags" style="height: 50px;"></ul>
      </td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $note->_id}}
          <button type="submit" class="submit">{{tr}}Modify{{/tr}}</button>

          <button type="button" class="trash"
                  onclick="confirmDeletion(this.form,{typeName:'la ',objName:'{{$note->_view|smarty:nodefaults|JSAttribute}}'})">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button type="submit" class="submit singleclick">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>
