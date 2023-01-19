{{*
 * @package Mediboard\CompteRendu
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=document value=$object}}

<div class="me-margin-top-3">
    <span class="me-font-weight-bold">
        {{tr}}CCompteRendu.count_sending{{/tr}}
        <span style="font-weight: normal">:
            {{if $document|instanceof:'Ox\Mediboard\CompteRendu\CCompteRendu'}}
                {{$document->_count_deliveries+$document->_ref_file->_count_deliveries}}
            {{else}}
                {{$document->_count_deliveries}}
            {{/if}}
        </span>
    </span>
    <span class="far fa-lg fa-caret-square-right" onclick="DocumentItem.showDeliveries(this);"
          style="cursor: pointer;">
    </span>
    <ul id="{{$document->_guid}}-deliveries" style="display: none;">
        {{foreach from=$document->_ref_deliveries item=delivery}}
            {{foreach from=$delivery->_receivers item=receiver}}
                <li>
                    {{$receiver}}
                    {{if $delivery->_delivery_medium != 'mail'}}&nbsp;
                      <strong>({{$delivery->_delivery_medium|ucfirst}})</strong>
                    {{/if}}
                </li>
            {{/foreach}}
        {{/foreach}}
        {{if $document|instanceof:'Ox\Mediboard\CompteRendu\CCompteRendu' && $document->_ref_file->_count_deliveries > 0}}
            {{foreach from=$document->_ref_file->_ref_deliveries item=delivery}}
                {{foreach from=$delivery->_receivers item=receiver}}
                    <li>
                        {{$receiver}}
                        {{if $delivery->_delivery_medium != 'mail'}}&nbsp;
                          <strong>({{$delivery->_delivery_medium|ucfirst}})</strong>
                        {{/if}}
                    </li>
                {{/foreach}}
            {{/foreach}}
        {{/if}}
    </ul>
</div>
