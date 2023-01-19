{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-{{$message->level_class}} inline">
    {{$message->description}}
    {{if $message->diagnosis_level && $message->diagnosis_module && $message->diagnosis_code}}
        ({{$message->diagnosis_level}}{{$message->diagnosis_module}}{{$message->diagnosis_code}})
    {{/if}}
    {{if $message->breakable_rule}}
        <button type="button" class="unlock notext" onclick="Invoicing.forceRule('{{$invoice->id}}', '{{$message->rule_serial_id}}'{{if $message->forcing_type}}, '{{$message->forcing_type}}'{{/if}});">{{tr}}CJfseInvoiceView-action-force_rule{{/tr}} {{$message->rule_id}}</button>
    {{/if}}
</div>
