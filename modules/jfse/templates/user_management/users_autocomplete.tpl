{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<ul>
    {{foreach from=$matches item=_user}}
        <li data-jfse-user-id="{{$_user->jfse_id}}">
            <div class="me-autocomplete-mediusers"
                 style="border-left: 3px solid #{{$_user->_mediuser->_ref_function->color}}; padding-left: 2px; margin: -1px;">
                <div style="background-color: #{{$_user->_mediuser->_ref_function->color}};"></div>
                <span class="view">{{$_user->_mediuser}}</span>
            </div>
        </li>
        {{foreachelse}}
        <li>
            {{tr}}CHealthInsurance-no matches{{/tr}}
        </li>
    {{/foreach}}
</ul>

