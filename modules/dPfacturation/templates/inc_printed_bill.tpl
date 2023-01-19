{{*
 * @package Mediboard\Facturation
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=tip_left value=false}}
{{mb_default var=deny_callback value="Prototype.emptyFunction"}}

{{if $field === "bill_date_printed"}}
  {{assign var=bill_date value=$facture->bill_date_printed}}
  {{assign var=bill_user value=$facture->bill_user_printed}}
  {{assign var=short_name value="CFacture.Bill-short"}}
  {{assign var=long_name value="CFacture"}}
{{else}}
  {{assign var=bill_date value=$facture->justif_date_printed}}
  {{assign var=bill_user value=$facture->justif_user_printed}}
  {{assign var=short_name value="CFacture.Justificatif-short"}}
  {{assign var=long_name value="CFacture.Justificatif"}}
{{/if}}

{{assign var=cell_date value=''}}
{{if $bill_date}}
  {{assign var=cell_title value='CFacture.given_to_the_patient'}}
  {{if $bill_date !== '1970-01-01 00:00:00'}}
    {{assign var=cell_date value=$bill_date|date_format:$conf.datetime}}
  {{/if}}
{{else}}
  {{assign var=cell_title value='CFacture.not_given_to_the_patient'}}
{{/if}}
<div class="tip_hover">
  <i class="far fa-{{if $bill_date}}check{{else}}times{{/if}}-circle"></i>
  {{tr}}{{$short_name}}{{/tr}}
  <div class="tip_content {{if $tip_left}}tip_content_left{{/if}}">
    {{tr}}{{$long_name}}{{/tr}} : {{tr}}{{$cell_title}}{{/tr}} {{if $cell_date}}({{$cell_date}}){{/if}}
    {{if $bill_date && $bill_user === $app->user_id}}
      <form action="#" method="post" name="{{$facture->_guid}}_cancel_{{$field}}"
            onsubmit="return onSubmitFormAjax(this, function() { {{$deny_callback}}();});">
        {{mb_class object=$facture}}
        {{mb_key   object=$facture}}
        <input type="hidden" name="{{$field}}" value=""/>
        <button class="cancel notext compact me-tertiary"></button>
      </form>
    {{/if}}
  </div>
</div>