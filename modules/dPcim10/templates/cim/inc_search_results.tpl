{{*
 * @package Mediboard\Cim10
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=user_profile value=null}}
{{assign var=uid value='-'|uniqid}}

{{if $object_class == "CConsultation" && $object_id}}
  <script>
    SearchCopyToField = function(field, text) {
      var form = getForm("editFrmExams"+field);
      var text_container = form.down('textarea');
      var otext = $V(text_container);
      otext += "\n"+text;
      $V(text_container, otext);
      form.onsubmit();
      refreshExamen();
    }
  </script>
<div id="copy_result" style="display: none;">
  <form name="copy_result_text" method="post" onsubmit="return onSubmitFormAjax(this);">
    <div style="text-align: center;">
      <textarea name="_result_text" cols="100" rows="15"></textarea>
      {{if isset($object|smarty:nodefaults)}}
        {{mb_class object=$object}}
        {{mb_key object=$object}}

        {{mb_field object=$object field=motif hidden=true}}
        {{mb_field object=$object field=rques hidden=true}}
        {{mb_field object=$object field=conclusion hidden=true}}
      {{/if}}
    </div>
  </form>
</div>
{{/if}}

{{if $user_profile}}
  <script type="text/javascript">
    reloadSearchCIM{{$user_profile->_id}} = function() {
      getForm('searchCIM-CMediusers-{{$user_profile->_id}}').onsubmit();
    }
  </script>
{{/if}}

<table class="tbl">
  <tr>
    <th style="position: sticky;">{{tr}}CCodeCIM10.code{{/tr}}</th>
    <th style="position: sticky;">{{tr}}CCodeCIM10.libelle{{/tr}}</th>
    <th style="position: sticky;"></th>
  </tr>
  {{foreach from=$codes item=_code}}
    <tr class="alternate">
      <td>
        {{$_code->code_long}}
      </td>
      <td>
        <span style="max-width: 90px; float: right; min-width: 65px;">
          {{if $_code->occurrences}}
            <span title="{{tr}}CCodeCIM10-occurrences-desc{{/tr}}" class="cim10-occurrence circled">
              {{$_code->occurrences}}
            </span>
          {{/if}}
          {{if ($user_profile && $user_profile->_id == $user->_id) || !$user_profile}}
            {{mb_include module=cim10 template=cim/inc_favori code=$_code float=false}}
          {{/if}}
        </span>
        {{if $_code->_favoris_id && $user_profile && $user_profile->_id == $user->_id}}
          {{assign var=callback value='getForm("searchCIM-CMediusers-'|cat:$user_profile->_id|cat:'").onsubmit'|smarty:nodefaults|htmlspecialchars_decode}}
          <form name="favoris-tag-{{$_code->_favoris_id}}" method="post" style="float: right;">
            {{mb_include module=system template=inc_tag_binder_widget object=$_code->_ref_favori
                         show_button=false form_name="favoris-tag-`$_code->_favoris_id`" callback="reloadSearchCIM`$user_profile->_id`"}}
          </form>
        {{/if}}
        {{$_code->libelle|smarty:nodefaults}}
        {{if $object_class == "CConsultation" && $object_id}}
            <button style="float:right" type="button" class="fa fa-copy" onclick="SearchCopyToField('Motif', '{{$_code->libelle|smarty:nodefaults|strip_tags|JSAttribute}}');" title="Copier dans le champ Motif">Motif</button>
            <button style="float:right" type="button" class="fa fa-copy" onclick="SearchCopyToField('Rqs', '{{$_code->libelle|smarty:nodefaults|strip_tags|JSAttribute}}');" title="Copier dans le champ Remarques">Remarques</button>
            <button style="float:right" type="button" class="fa fa-copy" onclick="SearchCopyToField('Conclusion', '{{$_code->libelle|smarty:nodefaults|strip_tags|JSAttribute}}');" title="Copier dans le champ Au total">Au total</button>
        {{/if}}
      </td>
      <td class="narrow">
        <button type="button" class="tick notext"
                data-code="{{$_code->code}}" data-libelle="{{$_code->libelle}}"
                onclick="{{if $ged}}
                           CIM.selectCode(this);
                         {{else}}
                           CIM.selectCode('{{$_code->code}}');
                         {{/if}}"
                title="{{tr}}Select{{/tr}}">
          {{tr}}Select{{/tr}}
        </button>
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="4">
        {{tr}}CCodeCIM10.none.found{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>
