<table>
  <thead>
  <tr>
    <th colspan="2">Synthèse médicale du séjour</th>
  </tr>
  </thead>
  <tbody>
  <tr>
    <td>Modalité et date d'entrée en hospitalisation</td>
    <td>
      <content ID="{{'Ox\Interop\Cda\CCDAFactory'|const:MODALITE_ENTREE}}">
          {{mb_value object=$sejour field=entree}}, {{tr}}CSejour.mode_entree.{{$sejour->mode_entree}}{{/tr}}
      </content>
    </td>
  </tr>
  <tr>
    <td>Modalité et date de sortie d'hospitalisation</td>
    <td>
      <content ID="{{'Ox\Interop\Cda\CCDAFactory'|const:MODALITE_SORTIE}}">
          {{mb_value object=$sejour field=sortie}}, {{tr}}CSejour.mode_sortie.{{$sejour->mode_sortie}}{{/tr}}
      </content>
    </td>
  </tr>
  <tr>
    <td>Synthèse médicale</td>
    <td>
      <content ID="{{'Ox\Interop\Cda\CCDAFactory'|const:SYNTHESE}}">{{$sejour->libelle}}</content>
    </td>
  </tr>
  <tr>
    <td>Recherche de microorganismes multi-résistants ou émergents effectuée</td>
    <td>
      <content ID="{{'Ox\Interop\Cda\CCDAFactory'|const:RECHERCHE_MICRO_MULTI}}">Non</content>
    </td>
  </tr>
  <tr>
    <td>Transfusion de produits sanguins</td>
    <td>
      <content ID="{{'Ox\Interop\Cda\CCDAFactory'|const:TRANSFU}}">Non</content>
    </td>
  </tr>
  <tr>
    <td>Administration de dérivés du sang</td>
    <td>
      <content ID="{{'Ox\Interop\Cda\CCDAFactory'|const:ADMI_SANG}}">Non</content>
    </td>
  </tr>
  </tbody>
</table>
