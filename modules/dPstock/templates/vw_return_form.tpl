{{*
 * @package Mediboard\Stock
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<style type="text/css">
  {{mb_include module=dPcompteRendu template='../css/print.css' header=4 footer=3 ignore_errors=true}}

  html {
    font-family: Arial, Helvetica, sans-serif;
  }

  .print td {
    font-size: 11px;
    font-family: Arial, Verdana, Geneva, Helvetica, sans-serif;
  }
</style>

<table class="main">
  <tr>
    <td>
      <hr />
      <table class="form">
        <col style="width: 10%" />
        <col style="width: 40%" />
        <col style="width: 10%" />
        <col style="width: 40%" />

        <tr>
          <th>Date</th>
          <td>{{$dtnow|date_format:$conf.datetime}}</td>
          <th>Numéro</th>
          <td>{{$return_form->return_number}}</td>
        </tr>

        <tr>
          <th rowspan="2">{{mb_label object=$return_form field=comments}}</th>
          <td rowspan="2">{{mb_value object=$return_form field=comments}}</td>
          <th></th>
          <td></td>
        </tr>

        <tr>
          <td colspan="4">
            <hr />
          </td>
        </tr>

        <tr>
          <th>Expéditeur</th>
          <td>
            {{assign var=address value=$return_form->_ref_address}}
            {{if $address && $address->_id}}

              {{* Pharmacie *}}
              {{if $address|instanceof:'Ox\Mediboard\Mediusers\CFunctions'}}
                <strong>
                  {{$address->_ref_group}}<br />
                  {{$address}}
                </strong>
                <br />
                {{$address->adresse|nl2br}}
                <br />
                {{mb_value object=$address field=cp}} {{mb_value object=$address field=ville}}

                {{* Bloc *}}
              {{elseif $address|instanceof:'Ox\Mediboard\Bloc\CBlocOperatoire'}}
                <strong>
                  {{$address}}
                </strong>
                <br />
                {{$return_form->_ref_group->adresse|nl2br}}
                <br />
                {{mb_value object=$return_form->_ref_group field=cp}} {{mb_value object=$return_form->_ref_group field=ville}}

                {{* Etablissement *}}
              {{elseif $address|instanceof:'Ox\Mediboard\Etablissement\CGroups'}}
                <strong>
                  {{$address}}
                </strong>
                <br />
                {{$address->adresse|nl2br}}
                <br />
                {{mb_value object=$address field=cp}} {{mb_value object=$address field=ville}}
              {{/if}}
              <br />
              {{if $address->tel}}
                <br />
                {{mb_title object=$address field=tel}}: {{mb_value object=$address field=tel}}
              {{/if}}

              {{if $address->fax}}
                <br />
                {{mb_title object=$address field=fax}}: {{mb_value object=$address field=fax}}
              {{/if}}

              {{if $address|instanceof:'Ox\Mediboard\Mediusers\CFunctions' && $address->soustitre}}
                <hr />
                {{$address->soustitre|nl2br}}
              {{/if}}

            {{/if}}
          </td>

          <th>Fournisseur</th>
          <td>
            {{assign var=supplier value=$return_form->_ref_supplier}}
            <strong>{{mb_value object=$supplier field=name}}</strong><br />
            {{$supplier->address|nl2br}}<br />
            {{mb_value object=$supplier field=postal_code}} {{mb_value object=$supplier field=city}}

            <br />
            {{if $supplier->phone}}
              <br />
              {{mb_title object=$supplier field=phone}}: {{mb_value object=$supplier field=phone}}
            {{/if}}

            {{if $supplier->fax}}
              <br />
              {{mb_title object=$supplier field=fax}}: {{mb_value object=$supplier field=fax}}
            {{/if}}
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <thead>
  <tr>
    <td>
      <h2>
        <a href="#" onclick="window.print();">
          {{tr}}CProductReturnForm{{/tr}} - {{$return_form->_ref_group}}
        </a>
      </h2>
    </td>
  </tr>
  </thead>

  <tfoot>
  <tr>
    <td>
        <span style="float: right; text-align: right;">
          {{$dtnow|date_format:$conf.datetime}}

          {{if $pharmacien->_id}}
            <br />

Pharmacien :
            <strong>{{$pharmacien}}</strong>
            {{if $pharmacien->commentaires}}
              - {{$pharmacien->commentaires}}
            {{/if}}
          {{/if}}
        </span>

      {{tr}}CProductReturnForm{{/tr}} n° <strong>{{$return_form->return_number}}</strong>
      <br />
      Responsable : <strong>{{$app->_ref_user}}</strong>
    </td>
  </tr>
  </tfoot>

  <tr>
    <td>
      {{mb_include module=stock template=inc_outputs_list}}
    </td>
  </tr>
</table>