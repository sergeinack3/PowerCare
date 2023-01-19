{{*
 * @package Mediboard\cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cda script=ccda}}

<div>
  <form name="base64" onsubmit="return Ccda.manageBase64(this)" class="prepared" method="post" action="?m={{$m}}">
    <input type="hidden" name="encode" value="1"/>
    <pre style="padding: 0; max-height: none;"><textarea name="message" rows="12" style="width: 100%; border: none; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; margin: 0; resize: vertical;"></textarea></pre>
    <button type="submit" class="change me-primary" onclick="this.form.elements.encode.value = '1'">{{tr}}cda-msg-Encode{{/tr}}</button>
    <button type="submit" class="change me-primary" onclick="this.form.elements.encode.value = '0'">{{tr}}cda-msg-Decode{{/tr}}</button>
  </form>
</div>

<div id="result_base64" class="me-margin-top-8"></div>
