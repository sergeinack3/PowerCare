{{*
 * @package Mediboard\Personnel
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  Main.add( function () {
    var form = getForm("editFrm-{{$personnel->user_id}}");
    var url = new Url("personnel", "httpreq_do_personnels_autocomplete");
    url.autoComplete(form._view, form._view.id+'_autocomplete', {
      dropdown: true,
      minChars: 3,
      updateElement : function(element){
        $V(form.user_id, element.id.split('-')[1]);
        $V(form._view, element.select(".view")[0].innerHTML.stripTags());
      }
    });
  });
</script>

<form name="editFrm-{{$personnel->user_id}}" method="post" onsubmit="return Personnel.store(this);">
  {{mb_key object=$personnel}}
  {{mb_class object=$personnel}}
  <input type="hidden" name="del" value="0" />
  <input type="hidden" name="callback" value="Personnel.afterStore" />

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$personnel}}

    <tr>
      <th>{{mb_label object=$personnel field="user_id"}}</th>
      <td>
        <input type="hidden" name="user_id" class="notNull" value="{{$personnel->user_id}}" />
        <input type="hidden" name="object_class" value="CMediusers" />
        <input size="30" name="_view" value="{{$personnel->_ref_user}}" {{ if $personnel->user_id }}readonly="readonly"{{/if}}/>
        <div id="editFrm-{{$personnel->user_id}}__view_autocomplete" style="display: none; width: 300px;" class="autocomplete"></div>
      </td>
    </tr>

    <tr>
      <th>{{mb_label object=$personnel field="emplacement"}}</th>
      <td>{{mb_field object=$personnel field="emplacement"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$personnel field="actif"}}</th>
      <td>{{mb_field object=$personnel field="actif"}}</td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $personnel->_id}}
        <button class="modify">{{tr}}Save{{/tr}}</button>
        <button class="trash" type="button" onclick="Personnel.askDelete(this.form, '{{$personnel->_view|smarty:nodefaults|JSAttribute}}');">
          {{tr}}Delete{{/tr}}
        </button>
        {{else}}
        <button class="submit" name="btnFuseAction">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>

{{if $personnel->_id}}
  <table class="tbl">
    <tr>
      <th class="category" colspan="3">
        {{$personnel->_back.affectations|@count}} dernières affectations
        {{if $personnel->_count.affectations != $personnel->_back.affectations|@count}}
        sur {{$personnel->_count.affectations}} trouvées
        {{/if}}
      </th>
    </tr>

    {{foreach from=$personnel->_back.affectations item=_affectation}}
    <tr>
      <td>
        <a href="?m={{$m}}&tab=vw_affectations_pers&user_id={{$personnel->user_id}}&list[{{$personnel->emplacement}}]={{$personnel->_id}}&affect_id={{$_affectation->_id}}">
          <span onmouseover="ObjectTooltip.createEx(this, '{{$_affectation->_guid}}')">
            {{$_affectation->_ref_object}}
          </span>
        </a>
      </td>
      <td>{{mb_value object=$_affectation field="debut"}}</td>
      <td>{{mb_value object=$_affectation field="fin"}}</td>
    </tr>
    {{/foreach}}
  </table>
{{/if}}
