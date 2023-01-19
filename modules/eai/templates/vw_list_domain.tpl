{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<form name="editDomain" action="?m={{$m}}" method="post"
      onsubmit="return onSubmitFormAjax(this, function() {Domain.refreshListDomains();});">
  <input type="hidden" name="m" value="{{$m}}"/>
  <input type="hidden" name="dosql" value="do_domain_aed"/>
  <input type="hidden" name="domain_id" value="{{$domain->_id}}"/>
  <input type="hidden" name="del" value="0"/>
  <input type="hidden" name="incrementer_id" value="{{$domain->_ref_incrementer->_id}}"/>
  <input type="hidden" name="actor_id" value="{{$domain->_ref_actor->_id}}" />
  <input type="hidden" name="actor_class" value="{{$domain->_ref_actor->_class}}"/>
  {{if $domain->_id}}
    <input type="hidden" name="callback" value="Domain.refreshCDomain"/>
  {{else}}
    <input type="hidden" name="callback" value="Domain.createDomain"/>
  {{/if}}

  <table class="form">
    {{mb_include module=system template=inc_form_table_header object=$domain}}
    <tr>
      <th>{{mb_label object=$domain field="tag"}}</th>
      {{if $domain->_id}}
        <td>{{mb_value object=$domain field="tag"}}</td>
      {{else}}
        <td>{{mb_field object=$domain field="tag"}}</td>
      {{/if}}
    </tr>

    <tr>
      <th>{{mb_label object=$domain field="libelle"}}</th>
      <td>{{mb_field object=$domain field="libelle"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$domain field="namespace_id"}}</th>
      <td>{{mb_field object=$domain field="namespace_id"}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$domain field="OID"}}</th>
      <td>{{mb_field object=$domain field="OID" size=50}}</td>
    </tr>

    <tr>
      <th>{{mb_label object=$domain field="active"}}</th>
      <td>{{mb_field object=$domain field="active"}}</td>
    </tr>

    <tr>
      <td class="button" colspan="2">
        {{if $domain->_id}}
          <button class="modify" type="submit">{{tr}}Save{{/tr}}</button>
          <button class="trash" type="button" onclick="confirmDeletion(this.form, {
            ajax:1,
            typeName:&quot;{{tr}}{{$domain->_class}}.one{{/tr}}&quot;,
            objName:&quot;{{$domain->_view|smarty:nodefaults|JSAttribute}}&quot;},
            { onComplete: function() {
            Domain.refreshListDomains();
            Control.Modal.close();
            }})">
            {{tr}}Delete{{/tr}}
          </button>
        {{else}}
          <button class="submit" type="submit">{{tr}}Create{{/tr}}</button>
        {{/if}}
      </td>
    </tr>
  </table>
</form>