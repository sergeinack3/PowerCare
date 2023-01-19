{{*
 * @package Mediboard\Admin
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=colspan value=13}}

{{mb_include module=system template=inc_pagination change_page='changePage' total=$total current=$start step=$step}}

<table class="main tbl me-no-align">
  <tr>
    <th>{{mb_title class=CRGPDConsent field=group_id}}</th>
    <th>{{mb_title class=CRGPDConsent field=object_class}}</th>
    <th>{{mb_title class=CRGPDConsent field=object_id}}</th>
    <th class="narrow">{{mb_title class=CRGPDConsent field=tag}}</th>
    <th class="narrow">{{mb_title class=CRGPDConsent field=status}}</th>
    <th>{{mb_title class=CRGPDConsent field=generation_datetime}}</th>
    <th>{{mb_title class=CRGPDConsent field=send_datetime}}</th>
    <th>{{mb_title class=CRGPDConsent field=read_datetime}}</th>
    <th>{{mb_title class=CRGPDConsent field=acceptance_datetime}}</th>
    <th>{{mb_title class=CRGPDConsent field=refusal_datetime}}</th>
    <th>{{mb_title class=CRGPDConsent field=last_error}}</th>
    <th>{{tr}}CRGPDConsent-Proof file{{/tr}}</th>
    <th class="narrow">{{mb_title class=CRGPDConsent field=proof_hash}}</th>
  </tr>

    {{foreach from=$consents item=_consent}}
      {{assign var=_manager value=$_consent->getManager()}}

      <tr>
        <td>
            {{mb_value object=$_consent field=group_id tooltip=true}}
        </td>

        <td>
            {{mb_ditto name=object_class value='Ox\Core\CAppUI::tr'|static_call:"CRGPDConsent.object_class.`$_consent->object_class`"}}
        </td>

        <td>{{mb_value object=$_consent field=object_id tooltip=true}}</td>

        <td style="text-align: center;">
          <strong style="border: solid 1px; border-radius: 3px; padding: 4px;">{{mb_value object=$_consent field=tag}}</strong>
        </td>

        <td style="text-align: center;">
          <div class="CRGPDConsent_status_counter CRGPDConsent_status_{{$_consent->status}}" style="margin-right: auto;">
              {{mb_value object=$_consent field=status}}
          </div>
        </td>

        <td class="narrow">{{mb_value object=$_consent field=generation_datetime}}</td>
        <td class="narrow">{{mb_value object=$_consent field=send_datetime}}</td>
        <td class="narrow">{{mb_value object=$_consent field=read_datetime}}</td>
        <td class="narrow">{{mb_value object=$_consent field=acceptance_datetime}}</td>
        <td class="narrow">{{mb_value object=$_consent field=refusal_datetime}}</td>
        <td>
            {{*{{mb_value object=$_consent field=last_error}}*}}

            {{if $_consent->isToSend() && $_manager->canNotify($_consent->object_class) && !$_consent->_ref_object->getEmail()}}
              <span class="fa fa-stack" title="{{tr}}CRGPDConsent-error-Context email is not set{{/tr}}">
            <i class="fa fa-envelope fa-stack-1x"></i>
            <i class="fa fa-ban fa-stack-2x" style="color: firebrick;"></i>
          </span>
            {{/if}}
        </td>

        <td>
            {{if $_consent->_ref_consent_file && $_consent->_ref_consent_file->_id}}
              <span onmouseover="ObjectTooltip.createEx(this, '{{$_consent->_ref_consent_file->_guid}}');">
            {{$_consent->_ref_consent_file}}
          </span>
            {{/if}}
        </td>

        <td>
            {{if !$_consent->checkProofFile()}}
              <i class="fas fa-exclamation-triangle fa-lg fa-fw" style="color: goldenrod;"></i>
            {{else}}
              <i class="fas fa-check-circle fa-lg fa-fw" style="color: forestgreen;"></i>
            {{/if}}

          <span style="font-family: monospace;">{{mb_value object=$_consent field=proof_hash}}</span>
        </td>
      </tr>
        {{foreachelse}}
      <tr>
        <td class="empty" colspan="{{$colspan}}">{{tr}}CRGPDConsent.none{{/tr}}</td>
      </tr>
    {{/foreach}}
</table>