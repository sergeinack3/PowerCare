{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<table class="note">
{{foreach from=$notes item=_note}}
  <tr>
    <th class="info {{$_note->degre}}">
      <span style="float: right;">
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$_note->_ref_user initials=block}}
      </span>

      <label title="{{mb_value object=$_note field=date}}">
        {{$_note->date|rel_datetime}}
      </label>
    </th>
  </tr>
  <tr>
    <td class="text">
      {{if !$_note->user_id || $user == $_note->user_id}}
        <form name="Del-{{$_note->_guid}}" action="" method="post">
          {{mb_class object=$_note}}
          {{mb_key   object=$_note}}

          <button style="float: right;" class="cancel notext" type="button" onclick="Note.confirmDeletion(this.form, '{{$object->_guid}}')">
            {{tr}}Delete{{/tr}}
          </button>
        </form>
        {{if $_note->object_class == "CPatient" || $_note->object_class == "CConsultation"}}
          <button style="float: right;" class="modify notext" type="button" onclick="Note.edit('{{$_note->_id}}')">
              {{tr}}Modify{{/tr}}
          </button>
        {{/if}}
      {{/if}}
      <strong>{{mb_value object=$_note field=libelle}}</strong>
      {{mb_value object=$_note field=text}}
    </td>
  </tr>
{{foreachelse}}
  <tr><td class="empty">{{tr}}CNote.none{{/tr}}</td></t></tr>

{{/foreach}}
</table>
