<table>
  <thead>
  <tr>
    <th colspan="2">Synth�se m�dicale du s�jour</th>
  </tr>
  </thead>
  <tbody>
  <tr>
    <td>Modalit� et date d'entr�e en hospitalisation</td>
    <td>
      <content ID="{{'Ox\Interop\Cda\CCDAFactory'|const:MODALITE_ENTREE}}">
          {{mb_value object=$sejour field=entree}}, {{tr}}CSejour.mode_entree.{{$sejour->mode_entree}}{{/tr}}
      </content>
    </td>
  </tr>
  <tr>
    <td>Modalit� et date de sortie d'hospitalisation</td>
    <td>
      <content ID="{{'Ox\Interop\Cda\CCDAFactory'|const:MODALITE_SORTIE}}">
          {{mb_value object=$sejour field=sortie}}, {{tr}}CSejour.mode_sortie.{{$sejour->mode_sortie}}{{/tr}}
      </content>
    </td>
  </tr>
  <tr>
    <td>Synth�se m�dicale</td>
    <td>
      <content ID="{{'Ox\Interop\Cda\CCDAFactory'|const:SYNTHESE}}">{{$sejour->libelle}}</content>
    </td>
  </tr>
  <tr>
    <td>Recherche de microorganismes multi-r�sistants ou �mergents effectu�e</td>
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
    <td>Administration de d�riv�s du sang</td>
    <td>
      <content ID="{{'Ox\Interop\Cda\CCDAFactory'|const:ADMI_SANG}}">Non</content>
    </td>
  </tr>
  </tbody>
</table>
