{{*
 * @package Mediboard\Sante400
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if "dPsante400 CIdSante400 add_ipp_nda_manually"|gconf}}
  {{mb_script module=sante400 script=Idex ajax=1}}
  <button class="not-printable ipp_nda notext" title="{{tr}}CIdSante400-create-IPP-NDA{{/tr}}"
          onclick="Idex.edit_manually('{{$sejour->_guid}}', '{{$patient->_guid}}', {{$callback}})">{{tr}}Modify{{/tr}}</button>
{{/if}}