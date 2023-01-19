{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if !$reload}}
  <div id="{{$object->_guid}}-codage_credentials">
{{/if}}

<script type="text/javascript">
  Main.add(function() {
    new Url('mediusers', 'ajax_users_autocomplete')
    .addParam('prof_sante', '1')
    .addParam('input_field', '_praticien_view')
    .autoComplete(getForm('CCodageCCAM-new').elements['_praticien_view'], null, {
      minChars: 0,
      method: 'get',
      select: 'view',
      dropdown: true,
      afterUpdateElement: function(field, selected) {
        if ($V(field) != '') {
          $V(field, selected.down('.view').innerHTML);
        }

        $V(field.form.elements['praticien_id'], selected.getAttribute('id').split('-')[2]);
      }
    });
  });

  authorizeAccess = function(form) {
    $V(form.date_unlock, new Date().toDATETIME(true));
    form.onsubmit();
  };

  revokeAccess = function(form) {
    $V(form.date_unlock, '');
    form.onsubmit();
  };
</script>

<table class="tbl">
  <tr>
    <th>
      {{tr}}common-Practitioner{{/tr}}
      <form name="CCodageCCAM-new" action="?" method="post" onsubmit="return onSubmitFormAjax(this, PMSI.reloadCodageCredentials.curry('{{$object->_class}}', '{{$object->_id}}'));">
          {{mb_key object=$codage}}
          {{mb_class object=$codage}}
          <input type="hidden" name="codable_class" value="{{$object->_class}}">
        <input type="hidden" name="codable_id" value="{{$object->_id}}">
          {{if $object->_class == "COperation"}}
            <input type="hidden" name="date" value="{{$object->date}}"/>
          {{else}}
            <input type="hidden" name="date" value="{{$object->_date}}"/>
          {{/if}}
        <input type="hidden" name="praticien_id" value="" onchange="this.form.onsubmit();">
        <br>
        <input type="text" name="_praticien_view" value="" placeholder="Ajouter un praticien">
      </form>
    </th>
    <th>
      {{tr}}common-Status{{/tr}}
    </th>
    <th>
      {{tr}}CPermObject-permission{{/tr}}
    </th>
    <th>
      {{tr}}Details{{/tr}}
    </th>
    <th class="narrow"></th>
  </tr>
  {{foreach from=$codages item=codage}}
    <tr>
      <td>
        {{mb_include module=mediusers template=inc_vw_mediuser mediuser=$codage->_ref_praticien}}
      </td>
      <td>
        <span class="circled"{{if $codage->locked}} style="color: forestgreen;"{{/if}}>
          {{if $codage->locked}}
            {{tr}}CCodageCCAM-locked{{/tr}}
          {{else}}
            {{tr}}common-Pending{{/tr}}
          {{/if}}
        </span>
      </td>
      <td>
        {{if !$object->_coded || $codage->_codage_derogation}}
          <span class="circled" style="color: forestgreen; border-color: forestgreen;">
              {{tr}}common-Authorized{{/tr}}
          </span>
        {{else}}
          <span class="circled" style="color: firebrick; border-color: firebrick;">
            {{tr}}Locked{{/tr}}
          </span>
        {{/if}}
      </td>
      <td>
        {{if !$object->_coded}}
          {{tr}}COperation-msg-unlocked{{/tr}}
        {{elseif $codage->_codage_derogation}}
          <form name="{{$codage->_guid}}" action="?" method="post" onsubmit="return onSubmitFormAjax(this, PMSI.reloadCodageCredentials.curry('{{$object->_class}}', '{{$object->_id}}'));">
            {{mb_class object=$codage}}
            {{mb_key object=$codage}}

            {{tr}}COperation-msg-access_authorized{{/tr}} {{mb_field object=$codage field=date_unlock register=true form=$codage->_guid onchange='this.form.onsubmit();'}} {{tr}}date.to{{/tr}} {{$codage->_date_lock|date_format:$conf.datetime}}
          </form>
        {{/if}}
      </td>
      <td>
        {{if $object->_coded}}
          <form name="{{$codage->_guid}}-access" action="?" method="post" onsubmit="return onSubmitFormAjax(this, PMSI.reloadCodageCredentials.curry('{{$object->_class}}', '{{$object->_id}}'));">
            {{mb_class object=$codage}}
            {{mb_key object=$codage}}

            {{mb_field object=$codage field=date_unlock hidden=true}}
            {{if !$codage->_codage_derogation}}
              <button type="button" class="lock notext" onclick="authorizeAccess(this.form);">
                {{tr}}common-Authorize{{/tr}}
              </button>
            {{else}}
              <button type="button" class="unlock notext" onclick="revokeAccess(this.form);">
                {{tr}}common-action-Lock{{/tr}}
              </button>
            {{/if}}
          </form>
        {{/if}}
      </td>
    </tr>
  {{foreachelse}}
    <tr>
      <td class="empty" colspan="5">
        {{tr}}COperation-msg-codage_access-empty{{/tr}}
      </td>
    </tr>
  {{/foreach}}
</table>

{{if 'dPsalleOp COperation modif_actes_worked_days'|gconf}}
  <div class="small-info">
    {{tr}}CCodageCCAM-msg-only_worked_days_counted{{/tr}}
  </div>
{{/if}}

{{if !$reload}}
  </div>
{{/if}}
