{{*
 * @package Mediboard\GestionCab
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=params_paie value=$fichePaie->_ref_params_paie}}
{{assign var=employe value=$params_paie->_ref_employe}}

<table align="center" cellspacing="0" cols="5" border="1">
  <colgroup>
    <col width="100" />
    <col width="159" />
    <col width="112" />
    <col width="128" />
    <col width="85" />
  </colgroup>
  
  <tbody>
    <tr>
      <td colspan="3" rowspan="2" width="357" height="34" align="left" valign="top" bgcolor="#bb0044">
        <font size="5" color="#ffffff">Bulletin de paie</font>
      </td>
      <td colspan="2" align="left" bgcolor="#bb0044">
        <font color="#ffffff">Du {{$fichePaie->debut|date_format:$conf.longdate}}</font>
      </td>
    </tr>
  
    <tr>
      <td colspan="2" align="left" bgcolor="#bb0044">
        <font color="#ffffff">Au  {{$fichePaie->fin|date_format:$conf.longdate}}</font>
      </td>
    </tr>
  </tbody>
  
  <tbody>
    <tr>
      <td colspan="5" height="50" align="left">
        <font color="#999999">Convention collective nationale du personnel cabinets médicaux</font>
      </td>
    </tr>
  </tbody>
  
  <tbody>
    <tr>
      <td colspan="2" height="24" align="left" bgcolor="#eeeeee">
        <font size="4">Employeur</font>
      </td>
      <td colspan="3" align="left" bgcolor="#eeeeee">
        <font size="4">Salarié</font>
      </td>
    </tr>
  
    <tr>
      <td colspan="2" height="17" align="center">
        <strong>{{mb_value object=$params_paie field=nom}}</strong>
      </td>
      <td colspan="3" align="center">
        <strong>{{$employe}}</strong>
      </td>
    </tr>
  
    <tr>
      <td colspan="2" height="18" align="left">
        {{mb_value object=$params_paie field=adresse}}
      </td>
      <td colspan="3" align="left">
        {{mb_value object=$employe field=adresse}}
      </td>
    </tr>

    <tr>
      <td colspan="2" height="17" align="left">
        {{mb_value object=$params_paie field=cp}}
        {{mb_value object=$params_paie field=ville}}
      </td>
      <td colspan="3" align="left">
        {{mb_value object=$employe field=cp}}
        {{mb_value object=$employe field=ville}}
      </td>
    </tr>

    <tr>
      <td height="18" align="right">
        <strong>Siret</strong>
      </td>
      <td align="left">
        {{mb_value object=$params_paie field=siret}}
      </td>
      <td align="right">
        <strong>Fonction</strong>
      </td>
      <td colspan="2" align="left">
        {{mb_value object=$employe field=function}}
      </td>
    </tr>

    <tr>
      <td height="17" align="right">
        <strong>Code APE</strong>
      </td>
      <td align="left">
        {{mb_value object=$params_paie field=ape}}
      </td>
      <td align="right">
        <strong>Sécurité sociale</strong>
      </td>
      <td colspan="2" align="left">
        {{mb_value object=$params_paie field=matricule}}
      </td>
    </tr>
  </tbody>
  
  <tbody>
    <tr>
      <td height="38" align="left" colspan="5" />
    </tr>

    <tr>
      <td height="17" align="left" colspan="2" />
      <td align="center" bgcolor="#eeeeee">
        <strong>Salaire horaire</strong>
      </td>
      <td align="center" bgcolor="#eeeeee">
        <strong>Nbre d'heures</strong>
      </td>
      <td align="center" bgcolor="#eeeeee">
        <strong>Montant</strong>
      </td>
    </tr>

    <tr>
      <td colspan="2" height="17" align="right" bgcolor="#ee6633">
        <font color="#ffffff">Valeur du SMIC</font>
      </td>
      <td align="right">
        {{mb_value object=$params_paie field=smic}}
      </td>
      <td align="center"></td>
      <td align="center"></td>
    </tr>
  </tbody>
  
  <tbody>
    <tr>
      <td colspan="2" height="17" align="right" bgcolor="#ee6633">
        <font color="#ffffff">Salaire de base</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=salaire}}
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=heures}} h
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_base}}
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td colspan="2" height="17" align="right" bgcolor="#ee6633">
        <font color="#ffffff">Heures complémentaires</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=salaire}}
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=heures_comp}} h
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_heures_comp}}
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td colspan="2" height="17" align="right" bgcolor="#ee6633">
        <font color="#ffffff">Heures suplémentaires</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_base_heures_sup}}
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=heures_sup}} h
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_heures_sup}}
      </td>
    </tr>
  </tbody>

  {{if $fichePaie->_prime_precarite}}
  <tbody>
    <tr>
      <td colspan="2" height="17" align="right" bgcolor="#ee6633">
        <font color="#ffffff">
          Prime de précarité
          {{mb_value object=$fichePaie field=precarite}}
         </font>
      </td>
      <td align="left"></td>
      <td align="left"></td>
      <td align="right">
        {{mb_value object=$fichePaie field=_prime_precarite}}
      </td>
    </tr>
  </tbody>
  {{/if}}

  {{if $fichePaie->_prime_anciennete}}
  <tbody>
    <tr>
      <td colspan="2" height="17" align="right" bgcolor="#ee6633">
        <font color="#ffffff">
          Prime d'ancienneté
          {{mb_value object=$fichePaie field=anciennete}}
        </font>
      </td>
      <td align="left"></td>
      <td align="left"></td>
      <td align="right">
        {{mb_value object=$fichePaie field=_prime_anciennete}}
      </td>
    </tr>
  </tbody>
  {{/if}}

  {{if $fichePaie->_conges_payes}}
  <tbody>
    <tr>
      <td colspan="2" height="17" align="right" bgcolor="#ee6633">
        <font color="#ffffff">
          Congés payés
          {{mb_value object=$fichePaie field=conges_payes}}
        </font>
      </td>
      <td align="left"></td>
      <td align="left"></td>
      <td align="right">
         {{mb_value object=$fichePaie field=_conges_payes}}
      </td>
    </tr>
  </tbody>
  {{/if}}

  {{if $fichePaie->prime_speciale}}
  <tbody>
    <tr>
      <td colspan="2" height="17" align="right" bgcolor="#ee6633">
        <font color="#ffffff">Prime spéciale</font>
      </td>
      <td align="left"></td>
      <td align="left"></td>
      <td align="right">
         {{mb_value object=$fichePaie field=prime_speciale}}
      </td>
    </tr>
  </tbody>
  {{/if}}

  <tbody>
    <tr>
      <td colspan="3" height="17" align="right" bgcolor="#eeeeee">
        <strong>Salaire brut mensuel</strong>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_total_heures}} h
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_brut}}
      </td>
    </tr>
    <tr>
      <td height="38" align="left" colspan="5" />
    </tr>
    <tr>
      <td colspan="2" height="17" align="center">
        <strong>Taux cotisations salariales</strong>
      </td>
      <td align="center" bgcolor="#eeeeee">
        <strong>Montant</strong>
      </td>
      <td align="center" bgcolor="#eeeeee">
        <strong>Taux Cot. Patron.</strong>
      </td>
      <td align="center" bgcolor="#eeeeee">
        <strong>Montant</strong>
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="right" bgcolor="#ee6633">
        {{mb_value object=$fichePaie field=_base_csgnis}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=csgnis}}</font>
      </td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">CSG non imposable</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_csgnis}}
      </td>
      <td align="left"></td>
      <td align="left"></td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="right" bgcolor="#ee6633">
        {{mb_value object=$fichePaie field=_base_csgds}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=csgds}}</font>
      </td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">CSG déductible</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_csgds}}
      </td>
      <td align="left"></td>
      <td align="left"></td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="right" bgcolor="#ee6633">
        {{mb_value object=$fichePaie field=_base_csgnds}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=csgnds}}</font>
      </td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">CSG non déductible</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_csgnds}}
      </td>
      <td align="left"></td>
      <td align="left"></td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="right" bgcolor="#ee6633">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=ssms}}</font>
      </td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">S.S. maladie</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_ssms}}
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=ssmp}}</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_ssmp}}
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="right" bgcolor="#ee6633">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=ssvs}}</font>
      </td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">S.S. vieillesse</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_ssvs}}
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=ssvp}}</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_ssvp}}
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="right" bgcolor="#ee6633">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=rcs}}</font>
      </td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">Retraite complémentaire</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_rcs}}
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=rcp}}</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_rcp}}
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="right" bgcolor="#ee6633">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=agffs}}</font>
      </td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">AGFF</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_agffs}}
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=agffp}}</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_agffp}}
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="right" bgcolor="#ee6633">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=aps}}</font>
      </td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">Prévoyance</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_aps}}
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=app}}</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_app}}
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="right" bgcolor="#ee6633">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=acs}}</font>
      </td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">Chomage</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_acs}}
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=acp}}</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_acp}}
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="left" bgcolor="#ee6633"></td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">Accident du travail</font>
      </td>
      <td align="left"></td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=aatp}}</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_aatp}}
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="left" bgcolor="#ee6633"></td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">Contribution solidarité</font>
      </td>
      <td align="left"></td>
      <td align="right">
        {{mb_value object=$fichePaie field=_salaire_brut}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=csp}}</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_csp}}
      </td>
    </tr>
  </tbody>
  
  <tbody>
    <tr>
      <td height="17" align="left" bgcolor="#ee6633"></td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">Mutuelle</font>
      </td>
      <td align="right">{{mb_value object=$params_paie field=ms}}</td>
      <td align="center" />
      <td align="right">{{mb_value object=$params_paie field=mp}}</td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="left" bgcolor="#ee6633"></td>
      <td align="right" bgcolor="#ee6633">
        <font color="#330099">Réduc. Heures Sup.</font>
      </td>
      <td align="right">
        <font color="#330099">- {{mb_value object=$fichePaie field=_reduc_heures_sup_sal}}</font>
      </td>
      <td align="right">
      </td>
      <td align="right">
        <font color="#330099">- {{mb_value object=$fichePaie field=_reduc_heures_sup_pat}}</font>
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="left" bgcolor="#ee6633"></td>
      <td align="right" bgcolor="#ee6633">
        <font color="#330099">Réduc. Bas Sal.</font>
      </td>
      <td align="right">
      </td>
      <td align="right">
      </td>
      <td align="right">
        <font color="#330099">- {{mb_value object=$fichePaie field=_reduc_bas_salaires}}</font>
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td colspan="2" height="17" align="right" bgcolor="#eeeeee">
        <strong><font color="#330099">Total retenues</font></strong>
      </td>
      <td align="right" bgcolor="#eeeeee">
        <font color="#330099">{{mb_value object=$fichePaie field=_total_retenues}}</font>
      </td>
      <td align="center" bgcolor="#eeeeee">
        <strong><font color="#330099">Total Cot. Patron.</font></strong>
      </td>
      <td align="right" bgcolor="#eeeeee">
        <font color="#330099">{{mb_value object=$fichePaie field=_total_cot_patr}}</font>
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td colspan="2" height="17" align="right" bgcolor="#eeeeee">
        <strong>Salaire à payer</strong>
      </td>
      <td align="right" bgcolor="#eeeeee">
        {{mb_value object=$fichePaie field=_salaire_a_payer}}
      </td>
      <td align="left" bgcolor="#eeeeee"></td>
      <td align="left" bgcolor="#eeeeee"></td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="right" bgcolor="#ee6633">
        {{mb_value object=$fichePaie field=_base_csgnds}} -
        <font color="#33ee33">{{mb_value object=$params_paie field=csgnds}}</font>
      </td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">CSG non déductible</font>
      </td>
      <td align="right">
        {{mb_value object=$fichePaie field=_csgnds}}
      </td>
      <td align="left"></td>
      <td align="left"></td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td height="17" align="right" bgcolor="#ee6633">
      </td>
      <td align="right" bgcolor="#ee6633">
        <font color="#ffffff">Heures comp + sup</font>
      </td>
      <td align="right">
        <font color="#330099">- {{mb_value object=$fichePaie field=_total_heures_sup}}</font>
      </td>
      <td align="left"></td>
      <td align="left"></td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td colspan="2" height="34" align="right" bgcolor="#eeeeee">
        <strong>Net imposable<br />(Net à payer + CSG/RDS imp. - Heures sup.)</strong>
      </td>
      <td align="right" bgcolor="#eeeeee">
        {{mb_value object=$fichePaie field=_salaire_net}}
      </td>
      <td align="left" bgcolor="#eeeeee"></td>
      <td align="left" bgcolor="#eeeeee"></td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td colspan="5" height="50" align="left">
        <font color="#999999">Ce bulletin de paie doit être conservé sans limitation de durée</font>
      </td>
    </tr>
  </tbody>

  <tbody>
    <tr>
      <td colspan="2" height="17" align="left">
        <strong>Date de paiement :</strong>
      </td>
      <td colspan="3" align="left">
        <strong>Signature :</strong>
      </td>
    </tr>
    <tr>
      <td colspan="2" height="76" align="left"></td>
      <td colspan="3" align="left"></td>
    </tr>
  </tbody>
</table>