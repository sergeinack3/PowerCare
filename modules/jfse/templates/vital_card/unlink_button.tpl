{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{*
  * To have a smaller button, set notext=true on the include. Otherwise just don't define it.
  *}}

{{mb_script module=jfse script=Jfse ajax=$ajax}}
{{mb_script module=jfse script=VitalCard ajax=$ajax}}

{{mb_default var=notext value=false}}

<button type="button" class="unlink {{if $notext}}notext{{/if}}" onclick="VitalCard.unlink(this)" id="unlink_patient" data-link-id="{{$link_id}}">
    {{if !$notext}}
        {{tr}}VitalCardService-Unlink{{/tr}}
    {{/if}}
</button>
