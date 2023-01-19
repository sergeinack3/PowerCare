{{*
 * @package Mediboard\PlanningOp
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=object value=$subject}}

<script type="text/javascript">
  ActesNGAP = {
    remove: function(form) {
      $V(form.del, 1);
      form.onsubmit();
    },

    checkExecutant: function(form) {
      if (!$V(form._executant_spec_cpam)) {
        alert("{{if $app->_ref_user->isPraticien()}}{{tr}}CActeNGAP-specialty-undefined_medecin{{/tr}}{{else}}{{tr}}CActeNGAP-specialty-undefined_user{{/tr}}{{/if}}");
      }
    },

    checkNumTooth: function(input, view) {
      var num_tooth = $V(input);

      if (num_tooth < 11 || (num_tooth > 18 && num_tooth < 21) || (num_tooth > 28 && num_tooth < 31) || (num_tooth > 38 && num_tooth < 41) || (num_tooth > 48 && num_tooth < 51) || (num_tooth > 55 && num_tooth < 61) || (num_tooth > 65 && num_tooth < 71) || (num_tooth > 75 && num_tooth < 81) ||  num_tooth > 85) {
        alert("Le numéro de dent saisi ne correspond pas à la numérotation internationale!");
      }
      else {
        ActesNGAP.syncCodageField(this, view);
      }
    },

    editDEP: function(view) {
      Modal.open('modal_dep' + view, {showClose: true});
    },

    toggleDateDEP: function(element, view) {
      if (element.value == 1) {
        $('accord_infos' + view).show();
      }
      else {
        $('accord_infos' + view).hide();
      }
    },

    syncDEPFields: function(form, view) {
      ActesNGAP.syncCodageField(form.down('[name="accord_prealable"]:checked'), view);
      ActesNGAP.syncCodageField(form.date_demande_accord, view);
      ActesNGAP.syncCodageField(form.reponse_accord, view);
      Control.Modal.close();
    },

    checkDEP: function(view) {
      var element = $('info_dep' + view);
      var form = getForm('editActeNGAP-accord_prealable' + view);

      if (element != null) {
        if ($V(form.accord_prealable) == '1' && $V(form.date_demande_accord) && $V(form.reponse_accord)) {
          element.setStyle({color: '#197837'});
        }
        else {
          element.setStyle({color: '#ffa30c'});
        }
      }
    },

    setCoefficient: function(element, view) {
      var value = $V(element)
      if (value != '') {
        ActesNGAP.syncCodageField(element, view);
      }
    },

    refreshTarif: function(view) {
      $('inc_codage_ngap_button_create').disabled = true;
      var form = getForm('editActeNGAP' + view);
      var url = new Url("cabinet", "httpreq_vw_tarif_code_ngap");
      url.addElement(form.quantite);
      url.addElement(form.code);
      url.addElement(form.coefficient);
      url.addElement(form.demi);
      url.addElement(form.complement);
      url.addElement(form.executant_id);
      url.addElement(form.gratuit);
      url.addElement(form.execution);
      url.addParam('view', view);
      if ($V(form.acte_ngap_id)) {
        url.addParam('disabled', 1);
      }
      url.requestUpdate('tarifActe' + view, function() {
        $('inc_codage_ngap_button_create').disabled = false;
      });
    },

    syncCodageField: function(element, view) {
      if (element.name == 'quantite' || element.name == 'coefficient') {
        if (parseFloat($V(element)) <= 0) {
          $V(element, 1);
        }
      }

      var form = getForm('editActeNGAP' + view);
      var fieldName = element.name;
      var fieldValue = $V(element);
      $V(form[fieldName], fieldValue);
    },

    submit: function(form) {
      if (!$V(form.acte_ngap_id)) {
        ActesNGAP.checkExecutant(form);
      }
      return onSubmitFormAjax(form, function() {
        ProtocoleDHE.codes.refreshCoding();
      });
    }
  };

  Main.add(function() {
    ProtocoleDHE.codes.subjectId = '{{$subject->_id}}';
    ProtocoleDHE.codes.role = '{{$role}}';
    ProtocoleDHE.codes.objectClass = '{{$object_class}}';
  });
</script>
<tr>
  <td>
    <table class="form">
      <tr>
        <th colspan="15" class="title">Codage NGAP du protocole</th>
      </tr>
      <tr>
        <th class="category">{{mb_title class=CActeNGAP field=quantite}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=code}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=coefficient}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=demi}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=montant_base}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=montant_depassement}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=complement}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=gratuit}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=qualif_depense}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=exoneration}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=accord_prealable}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=execution}}</th>
        <th class="category">{{mb_title class=CActeNGAP field=executant_id}}</th>
        <th class="narrow category" colspan="2"></th>
      </tr>

      {{mb_include module=cabinet template=inc_line_codage_ngap acte=$acte_ngap _is_dentiste=false}}

      {{foreach from=$object->_ref_actes_ngap item=_acte_ngap}}
        {{if !$executant_id || $_acte_ngap->executant_id == $executant_id}}
          {{mb_include module=cabinet template=inc_line_codage_ngap acte=$_acte_ngap _is_dentiste=false}}
        {{/if}}
      {{/foreach}}
    </table>
  </td>
</tr>
