<table>
  <thead>
  <tr>
    <th>Date de début</th>
    <th>Date de fin</th>
    <th>Médicament</th>
    <th>Posologie</th>
  </tr>
  </thead>
    {{foreach from=$prescription_lines item=_prescription_line}}
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
    {{/foreach}}
</table>
