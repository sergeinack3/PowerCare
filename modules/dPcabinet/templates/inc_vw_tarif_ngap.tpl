{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script type="text/javascript">
  Main.add(function() {
    var form = getForm('editActeNGAP{{$view}}');
    var form_coef = getForm('editActeNGAP-coefficient{{$view}}');
    var field_coef = form.coefficient;
    var field_complement = form.complement;

    if ($V(field_coef) != {{$acte->coefficient}}) {
      $V(field_coef, '{{$acte->coefficient}}');
      $V(form_coef.coefficient, '{{$acte->coefficient}}', false);
    }

    field_coef.min = '{{$acte->_min_coef}}';
    field_coef.max = '{{$acte->_max_coef}}';

    var options = $$('form[name="editActeNGAP-complement{{$view}}"] select[name="complement"] option');
      options.each(function(option) {
        option.writeAttribute('disabled', null);
      });

    {{foreach from=$acte->_forbidden_complements item=_complement}}
      var options = $$('form[name="editActeNGAP-complement{{$view}}"] select[name="complement"] option[value="{{$_complement}}"]');
      options.each(function(option) {
        option.writeAttribute('disabled', 'disabled');
      });
    {{/foreach}}

    {{if $acte->_dep && !$disabled}}
      $('info_dep{{$view}}').show();
      $('button_edit_dep{{$view}}').show();
    {{else}}
      $('info_dep{{$view}}').hide();
      $('button_edit_dep{{$view}}').hide();
    {{/if}}

    {{if $acte->lieu == 'D'}}
      if ($V(form.acte_ngap_id) == '' && $('editActeNGAP-lieu{{$view}}_lieu')) {
        var lieu = getForm('editActeNGAP-lieu{{$view}}').lieu;
        $V(lieu, 'D');
        ActesNGAP.syncCodageField(lieu, '{{$view}}');
      }
      else {
        $V(form.elements['lieu'], 'D');
      }
    {{/if}}

    {{* Passage automatique de l'acte en mode gratuit si le taux d'abattement des IK infirmier est à zéro *}}
    {{if $acte->isIKInfirmier()}}
      ActesNGAP.syncCodageField(getForm('editActeNGAP-montant_base{{$view}}').elements['taux_abattement'], '{{$view}}', false);
      {{if $acte->taux_abattement == 0}}
        $V(getForm('editActeNGAP-gratuit{{$view}}').elements['gratuit'], '1', false);
        ActesNGAP.syncCodageField(getForm('editActeNGAP-gratuit{{$view}}').elements['gratuit'], '{{$view}}', false);
      {{/if}}
    {{/if}}

    ActesNGAP.syncCodageField(getForm('editActeNGAP-montant_base{{$view}}').elements['lettre_cle'], '{{$view}}');
    ActesNGAP.syncCodageField(getForm('editActeNGAP-montant_base{{$view}}').elements['montant_base'], '{{$view}}', true);
  });
</script>

<form name="editActeNGAP-montant_base{{$view}}" action="?" method="post" onsubmit="return false;">
  {{if $disabled}}
    {{mb_field object=$acte field=montant_base onchange="ActesNGAP.syncCodageField(this, '$view');" size=3 disabled=true}}
  {{else}}
    {{mb_field object=$acte field=montant_base onchange="ActesNGAP.syncCodageField(this, '$view');" size=3}}
  {{/if}}

  {{* Affichage du taux d'abattement pour les indemnités kilométriques des infirmiers *}}
  {{if $acte->isIKInfirmier()}}
    <select name="taux_abattement" onchange="ActesNGAP.changeTauxAbattement(this, '{{$view}}');" style="margin-left: 10px;">
      <option value="1.00"{{if $acte->taux_abattement == 1}} selected="selected"{{/if}}>100%</option>
      <option value="0.50"{{if $acte->taux_abattement == 0.5}} selected="selected"{{/if}}>50%</option>
      <option value="0"{{if $acte->taux_abattement == 0}} selected="selected"{{/if}}>0%</option>
    </select>
    <i class="fa fa-info-circle" style="color: blue;" title="CActeNGAP-message-taux_abattement"></i>
  {{/if}}
  {{mb_field object=$acte field=lettre_cle onchange="ActesNGAP.syncCodageField(this, '$view');" hidden=true}}
</form>
