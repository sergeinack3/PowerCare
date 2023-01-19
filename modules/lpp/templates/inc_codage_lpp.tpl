{{*
 * @package Mediboard\Lpp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<script>
  syncActLppField = function(form, field, element) {
    $V(getForm(form)[field], $V(element));
  };

  changeFinalPrice = function(form, field, element) {
    let value = $V(element);
    if (field == 'quantite' && parseFloat(value) <= 0) {
      value = 1;
      $V(getForm(form + '-quantite').quantite, value);
    }

    syncActLppField(form, field, element);
    var quantite = parseInt($V(getForm(form + '-quantite').quantite));
    var base = parseFloat($V(getForm(form + '-montant_base').montant_base));
    $V(getForm(form + '-montant_final').montant_final, quantite * base);
  };

  updateTotalPrice = function(form, field, element) {
    syncActLppField(form, field, element);

    let montant_depassement = $V(getForm(form + '-montant_depassement').elements['montant_depassement']);
    let montant_final = $V(getForm(form + '-montant_final').elements['montant_final']);

    let montant_total = 0;
    if (montant_depassement) {
        montant_total = parseFloat(montant_depassement);
    }
    if (montant_final) {
        montant_total += parseFloat(montant_final);
    }
    $V(getForm(form + '-montant_total').montant_total, montant_total);
  };

  updateActesLPP = function() {
    var url = new Url('lpp', 'codageLpp');
    url.addParam('object_id', '{{$codable->_id}}');
    url.addParam('object_class', '{{$codable->_class}}');
    let onComplete = Prototype.emptyFunction;
    {{if $codable->_class == 'CDevisCodage'}}
      onComplete = DevisCodage.refresh.curry('{{$codable->_id}}');
    {{/if}}
    url.requestUpdate('lpp', {onComplete: onComplete});
  };

  editDEP = function(form) {
    Modal.open(form + '-dep_modal', {showClose: true});
  };

  syncDEPFields = function(form) {
    var form_dep = getForm(form + '-dep');
    syncActLppField(form, 'accord_prealable', $V(form_dep.accord_prealable));
    syncActLppField(form, 'date_demande_accord', $V(form_dep.date_demande_accord));
    syncActLppField(form, 'reponse_accord', $V(form_dep.reponse_accord));

    Control.Modal.close();
  };

  Main.add(function() {
    $('count_lpp_{{$codable->_guid}}').innerHTML = '({{$codable->_ref_actes_lpp|@count}})';
  });
</script>

<table class="form">
  <tr>
    <th class="category">
      {{mb_title class=CActeLPP field=code}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=code_prestation}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=type_prestation}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=executant_id}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=date}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=date_fin}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=accord_prealable}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=qualif_depense}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=quantite}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=montant_base}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=montant_final}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=montant_depassement}}
    </th>
    <th class="category">
      {{mb_title class=CActeLPP field=montant_total}}
    </th>
    <th class="category compact"></th>
  </tr>

  {{mb_include module=lpp template=inc_acte acte_lpp=$acte_lpp}}

  {{foreach from=$codable->_ref_actes_lpp item=_acte_lpp}}
    {{mb_include module=lpp template=inc_acte acte_lpp=$_acte_lpp}}
  {{/foreach}}
</table>
