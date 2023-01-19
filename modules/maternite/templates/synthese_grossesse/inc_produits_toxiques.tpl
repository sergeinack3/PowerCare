{{*
 * @package Mediboard\maternite
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<tr>
  <th class="category" colspan="4">{{tr}}CDossierPerinat-Toxic products{{/tr}}</th>
</tr>
<tr>
  <th>{{mb_label object=$dossier_perinatal field=tabac_avant_grossesse}}</th>
  <td>
      <span {{if $dossier_perinatal->tabac_avant_grossesse}}style="color: darkred;"{{/if}}>
        {{mb_value object=$dossier_perinatal field=tabac_avant_grossesse}}
      </span>
      {{if $dossier_perinatal->tabac_avant_grossesse}}
        : {{mb_value object=$dossier_perinatal field=qte_tabac_avant_grossesse}} {{tr}}CDossierPerinat-qte_tabac_avant_grossesse_unite{{/tr}}
      {{/if}}
  </td>
</tr>
<tr>
  <th>{{mb_label object=$dossier_perinatal field=tabac_debut_grossesse}}</th>
  <td>
      <span {{if $dossier_perinatal->tabac_debut_grossesse}}style="color: darkred;"{{/if}}>
        {{mb_value object=$dossier_perinatal field=tabac_debut_grossesse}}
      </span>
      {{if $dossier_perinatal->tabac_debut_grossesse}}
        : {{mb_value object=$dossier_perinatal field=qte_tabac_debut_grossesse}} {{tr}}CDossierPerinat-qte_tabac_avant_grossesse_unite{{/tr}}
      {{/if}}
  </td>
</tr>
<tr>
  <th>{{mb_label object=$dossier_perinatal field=alcool_debut_grossesse}}</th>
  <td>
      <span {{if $dossier_perinatal->alcool_debut_grossesse}}style="color: darkred;"{{/if}}>
        {{mb_value object=$dossier_perinatal field=alcool_debut_grossesse}}
      </span>
      {{if $dossier_perinatal->alcool_debut_grossesse}}
        : {{mb_value object=$dossier_perinatal field=qte_alcool_debut_grossesse}} {{tr}}CDossierPerinat-qte_alcool_debut_grossesse_unite{{/tr}}
      {{/if}}
  </td>
</tr>
<tr>
  <th>{{mb_label object=$dossier_perinatal field=canabis_debut_grossesse}}</th>
  <td>
      <span {{if $dossier_perinatal->canabis_debut_grossesse}}style="color: darkred;"{{/if}}>
        {{mb_value object=$dossier_perinatal field=canabis_debut_grossesse}}
      </span>
      {{if $dossier_perinatal->canabis_debut_grossesse}}
        : {{mb_value object=$dossier_perinatal field=qte_canabis_debut_grossesse}} {{tr}}CDossierPerinat-qte_canabis_debut_grossesse_unite{{/tr}}
      {{/if}}
  </td>
</tr>
<tr>
  <th>{{mb_label object=$dossier_perinatal field=subst_avant_grossesse}}</th>
  <td>
      <span {{if $dossier_perinatal->subst_avant_grossesse}}style="color: darkred;"{{/if}}>
        {{mb_value object=$dossier_perinatal field=subst_avant_grossesse}}
      </span>
      {{if $dossier_perinatal->subst_avant_grossesse}}
        <br/>
        &mdash; {{tr}}CDossierPerinat-mode_subst_avant_grossesse-court{{/tr}} :
          {{mb_value object=$dossier_perinatal field=mode_subst_avant_grossesse}}
        <br/>
        &mdash; {{mb_label object=$dossier_perinatal field=nom_subst_avant_grossesse}} :
          {{mb_value object=$dossier_perinatal field=nom_subst_avant_grossesse}}

          {{if $dossier_perinatal->subst_subst_avant_grossesse}}
            <br/>
            &mdash; {{mb_label object=$dossier_perinatal field=subst_subst_avant_grossesse}} :
              {{mb_value object=$dossier_perinatal field=subst_subst_avant_grossesse}}
          {{/if}}
      {{/if}}
  </td>
</tr>
<tr>
  <th>{{mb_label object=$dossier_perinatal field=subst_debut_grossesse}}</th>
  <td>
      <span {{if $dossier_perinatal->subst_debut_grossesse}}style="color: darkred;"{{/if}}>
        {{mb_value object=$dossier_perinatal field=subst_debut_grossesse}}
      </span>
  </td>
</tr>

