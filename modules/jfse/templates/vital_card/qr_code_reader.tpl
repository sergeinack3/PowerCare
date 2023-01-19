{{*
 * @package Mediboard\Jfse
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_default var=add_form value=true}}

<div id="jfse-vital-qrcode_container" style="display: none;">
    {{if $add_form}}<form name="Jfse-QrCode-scanner" method="post" action="?" onsubmit="return false;">{{/if}}
        <div id="jfse-vital-qrcode_scan_message_container" class="small-info">
            Veuillez scanner le QRCode
        </div>
        <div id="jfse-vital-qrcode_scan_loading_container" style="display: none;">
            <div class="ajax-loading" style="width: 100%;"></div>
            <div style="width: 100%; height: 1px;"></div>
            <div class="small-success" style="">Acquisition du QRCode en cours</div>
        </div>
        <input type="text" name="jfse-apcv-qrcode" value="" style="opacity: 0; height: 0px; width: 0px;"/>
    {{if $add_form}}</form>{{/if}}
</div>
