{{*
 * @package Mediboard\
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

{{assign var=label value=$_order->getLabel()}}

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
          <th>{{tr}}Date{{/tr}}</th>
          <td>{{$dnow|date_format:$conf.datetime}}</td>
          <th>Numéro</th>
          <td>{{$_order->order_number}}</td>
        </tr>

        <tr>
          <th rowspan="2">{{mb_label object=$_order field=comments}}</th>
          <td rowspan="2">{{mb_value object=$_order field=comments}}</td>
          <th>{{mb_label object=$_order field=_customer_code}}</th>
          <td>{{mb_value object=$_order field=_customer_code}}</td>
        </tr>

        <tr>
          <th>{{if $_order->object_id}}{{mb_label object=$_order field=object_id}}{{/if}}</th>
          <td>
            {{if !$_order->_septic}}
              {{$_order->_ref_object}}
            {{/if}}
          </td>
        </tr>

        <tr>
          <td colspan="4">
            <hr />
          </td>
        </tr>

        <tr>
          <th class="me-valign-top me-line-height-12">{{tr}}CProductOrder-address_id{{/tr}}</th>
          <td>
            {{assign var=address value=$_order->_ref_address}}

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
              {{$_order->_ref_group->adresse|nl2br}}
              <br />
              {{mb_value object=$_order->_ref_group field=cp}} {{mb_value object=$_order->_ref_group field=ville}}

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
          </td>

          <th class="me-valign-top me-line-height-12">{{tr}}CProductMovement.object_class.CSociete{{/tr}}</th>
          <td>
            {{assign var=societe value=$_order->_ref_societe}}
            <strong>{{mb_value object=$societe field=name}}</strong><br />
            {{$societe->address|nl2br}}<br />
            {{mb_value object=$societe field=postal_code}} {{mb_value object=$societe field=city}}

            <br />
            {{if $societe->phone}}
              <br />
              {{mb_title object=$societe field=phone}}: {{mb_value object=$societe field=phone}}
            {{/if}}

            {{if $societe->fax}}
              <br />
              {{mb_title object=$societe field=fax}}: {{mb_value object=$societe field=fax}}
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
          {{$label}} - {{$_order->_ref_group}}
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
        </span>

      {{$label}} n° <strong>{{$_order->order_number}}</strong>
      <br />
      Responsable : <strong>{{$app->_ref_user}}</strong>
    </td>
  </tr>
  </tfoot>

  <tr>
    <td>
      {{mb_include module=stock template=inc_order_items_list order=$_order}}
    </td>
  </tr>
</table>