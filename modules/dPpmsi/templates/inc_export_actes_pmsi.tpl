{{*
 * @package Mediboard\Pmsi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{mb_script module=pmsi script=PMSI ajax=true}}
{{mb_default var=confirmCloture value=0}}
{{mb_default var=NDA value=""}}
{{mb_default var=IPP value=""}}

<script>
  PMSI.confirmCloture = {{$confirmCloture}};
</script>

{{if !"sa CSa send_only_with_ipp_nda"|conf:$group_guid || ($IPP && $NDA)}}
<div id="export_{{$object->_class}}_{{$object->_id}}">
  {{if $object->facture}}
    {{if $canUnlockActes}}
    <button class="cancel me-secondary" onclick="PMSI.deverouilleDossier('{{$object->_id}}', '{{$object->_class}}', '{{$m}}')">
      Déverrouiller le dossier
    </button>
    {{else}}
    <div class="small-info">
      Veuillez contacter le PMSI pour déverrouiller le dossier
    </div>
    {{/if}}
  {{else}}
    <button class="tick singleclick me-secondary"
     onclick="{{if $object|instanceof:'Ox\Mediboard\PlanningOp\COperation' && 'dPsalleOp CActeCCAM del_acts_not_rated'|gconf}}
         PMSI.checkActivites('{{$object->_id}}', '{{$object->_class}}', null, '{{$m}}');
       {{else}}
         PMSI.exportActes('{{$object->_id}}', '{{$object->_class}}', null, '{{$m}}');
       {{/if}}">
      {{if $object->_class == "CSejour"}}
        Export des diagnostics et actes du séjour
      {{else}}
        Export des actes de l'intervention
      {{/if}}
    </button>
  {{/if}}
  
  <div class="text">
    {{if $object->_nb_exchanges}}
      <div class="small-success">
        Export déjà effectué {{$object->_nb_exchanges}} fois
      </div>
    {{else}}
      <div class="small-info">
        Pas d'export effectué
      </div>
    {{/if}}
  </div>
</div>
{{else}}
<div class="small-warning">
  Vous ne pouvez pas exporter les actes pour les raisons suivantes :
  <ul>
    {{if !$NDA}}
    <li>Numero de dossier manquant</li>
    {{/if}}
    {{if !$IPP}}
    <li>IPP manquant</li>
    {{/if}}
  </ul>
</div>
{{/if}}
