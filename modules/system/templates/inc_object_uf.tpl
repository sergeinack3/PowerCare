{{*
 * @package Mediboard\System
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{if @$modules.dPhospi->_can->read && "dPhospi General show_uf"|gconf}}
  {{mb_script module=hospi script=affectation_uf ajax=true}}
  {{if ($object|instanceof:'Ox\Mediboard\PlanningOp\CSejour') || ($object|instanceof:'Ox\Mediboard\Hospi\CAffectation')}}
    <a style="float: right;" href="#1"
       {{if $object|instanceof:'Ox\Mediboard\Hospi\CAffectation'}}
         onclick="AffectationUf.affecter('{{$object->_id}}', '{{$object->lit_id}}')"
       {{/if}}
       onmouseover="ObjectTooltip.createEx(this,'{{$object->_guid}}', 'objectUFs')">
      <span class="texticon texticon-uf">UF</span>
    </a>
  {{else}}
    <a style="float: right;" href="#1"
       onclick="AffectationUf.edit('{{$object->_guid}}')"
       onmouseover="ObjectTooltip.createEx(this,'{{$object->_guid}}', 'objectUFs')">
      <span class="texticon texticon-uf" title="Affecter les UF">UF</span>
    </a>
  {{/if}}
{{/if}}