<table>
    {{foreach from=$prescription_lines item=_prescription_line}}
      <thead>
      <tr>
        <th>Date de début</th>
        <th>Date de fin</th>
        <th>Médicament</th>
        <th>Posologie</th>
      </tr>
      </thead>
      <tbody>
      <tr>
        <td>{{$_prescription_line->debut}}</td>
        <td>{{$_prescription_line->fin}}</td>
        <td>
          <content ID="{{$_prescription_line->_guid}}">{{$_prescription_line->_ref_produit->ucd_view}}</content>
        </td>
        <td>
            {{foreach from=$_prescription_line->_ref_prises item=_prise}}
                {{$_prise}}
            {{/foreach}}
        </td>
      </tr>
      </tbody>
        {{foreachelse}}
      <tbody>
      <tr>
        <td>
          <content ID='{{'Ox\Interop\Cda\CCDAFactory'|const:NONE_TREATMENT}}'>
            Aucun traitement pour le patient
          </content>
        </td>
      </tr>
      </tbody>
    {{/foreach}}
</table>
