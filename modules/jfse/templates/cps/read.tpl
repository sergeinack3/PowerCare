{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-info">
    {{tr var1=$cps->first_name var2=$cps->last_name}}CCpsCard-msg-read{{/tr}}
</div>

{{if $substitute}}
    {{mb_include module=jfse template=cps/substitute_session}}
{{/if}}
