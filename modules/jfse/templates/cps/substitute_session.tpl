{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<div class="small-warning">
    {{tr var1=$substitute->first_name var2=$substitute->last_name var3=$substitute->invoicing_number}}
        CSubstitute-msg-session_active
    {{/tr}}
    <br>
    {{tr}}CSubstitute-question-deactivate_session{{/tr}}
    <div style="width: 100%; text-align: center;">
        <button type="button" onclick="Cps.deactivateSubstituteSession('{{$substitute->id}}');">{{tr}}Yes{{/tr}}</button>
        <button type="button" onclick="Control.Modal.close();">{{tr}}No{{/tr}}</button>
    </div>
</div>
