{{*
 * @package Mediboard\Cda
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=cda script=ccda}}

<form name="showCDA-form" onsubmit="return Ccda.showCDA(this)" method="post" class="prepared" action="?m=cda&a=ajax_showCDA">
  <input type="hidden" name="m" value="cda"/>
  <input type="hidden" name="accept_utf8" value="1"/>
  <input type="hidden" name="a" value="ajax_showCDA"/>
  <pre style="max-height: none;" class="me-align-auto me-margin-top-8 me-margin-bottom-8"><textarea name="message" rows="12" style="width: 100%;
      border: none; -webkit-box-sizing: border-box; -moz-box-sizing: border-box; resize: vertical;"></textarea></pre>

  <button type="submit" class="lookup">Visualiser le CDA</button>
</form>