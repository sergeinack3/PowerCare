{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_include module=system template=inc_interval_date from=$_line->debut to=$_line->fin}}
<span onmouseover="ObjectTooltip.createEx(this, '{{$_line->_guid}}', 'objectView')">
  <a href="#1" onclick="Prescription.viewProduit(null,'{{$_line->code_ucd}}','{{$_line->code_cis}}', null, '{{$_line->bdm}}');">
    {{$_line->_ucd_view}}
  </a>
</span>

<span class="compact" style="display: inline;">
            {{$_line->commentaire}}
  {{if $_line->_ref_prises|@count}}
    <br />
    ({{foreach from=`$_line->_ref_prises` item=_prise name=foreach_prise}}
     {{$_prise}}{{if !$smarty.foreach.foreach_prise.last}},{{/if}}
    {{/foreach}})
  {{/if}}

  {{if $_line->long_cours}}
    ({{mb_label class=CPrescriptionLineMedicament field=long_cours}})
  {{/if}}
  {{if $_line->conditionnel}}
    ({{mb_label class=CPrescriptionLineMedicament field=conditionnel}})
  {{/if}}
</span>
