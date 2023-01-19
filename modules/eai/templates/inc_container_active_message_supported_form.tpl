{{*
 * @package Mediboard\Eai
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{unique_id var=uid numeric=true}}
<td class="narrow" id="actor_message_supported_{{$uid}}">
  {{mb_include module=eai template=inc_active_message_supported_form}}
</td>
<td style="vertical-align: middle;" class="narrow"><strong>{{tr}}{{$_message_supported->message}}{{/tr}}</strong></td>
<td style="vertical-align: middle;" class="narrow"> <i class="fa fa-arrow-right"></i></td>
<td style="vertical-align: middle;" class="text compact">{{tr}}{{$_message_supported->message}}-desc{{/tr}}</td>
