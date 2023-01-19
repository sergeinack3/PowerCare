{{*
 * @package Mediboard\Patients
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}
{{if $_constant->_show_alert}}
  {{assign var=alert value=$_constant->generateViewAlert()}}
  {{if $alert}}
    <i class="fa fa-exclamation-circle constant_alert_{{$alert.level}}" title="{{$alert.title}}"></i>
  {{/if}}
{{/if}}