{{*
 * @package Mediboard\Cabinet
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<html>
  <head>
    <style type="text/css">
      {{mb_include module=dPcompteRendu template='../css/print.css' ignore_errors=true}}

      @media print {
        body {
          padding-top: {{$header + 3}}px;
          padding-bottom: {{$footer + 3}}px;

          font-size: 12px;
          font-family: Arial,Helvetica,sans-serif;
        }
      }

      div.header {
        height: {{$header}}px;
        border-bottom-width: 0;
      }

      div.footer {
        height: {{$footer}}px;
        border-top-width: 0;
      }
    </style>
  </head>

  <body>

    <script type="text/javascript">
      try {
        window.print();
      }
      catch(e){ }
    </script>

    {{mb_include module=cabinet template=inc_print_futurs_rdv_header}}

    {{mb_include module=cabinet template=inc_print_futurs_rdv_footer}}

    <h3>
      <div>
          {{tr var1=$patient}}CConsultation-List of appointments for patient %s{{/tr}}
          <br />
          ({{$contexte}})
          {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins && $patient->status == "QUAL"}}
            <br />
              {{tr}}CINSPatient{{/tr}} : {{mb_value object=$patient->_ref_patient_ins_nir field=ins_nir}} ({{$patient->_ref_patient_ins_nir->_ins_type}})
          {{/if}}
        <div style="text-align: right;">
            {{if $patient->_ref_patient_ins_nir && $patient->_ref_patient_ins_nir->datamatrix_ins}}
                {{mb_include module=dPpatients template=vw_datamatrix_ins center=false}}
            {{/if}}
        </div>
      </div>

    </h3>

    <ol>
    {{foreach name=consults from=$consults item=_consult}}
      <li style="font-size: 1.4em">
        {{tr var1=$_consult->_datetime|date_format:$conf.longdate var2=$_consult->_datetime|date_format:$conf.time}}CConsultation-Consultation on %s at %s{{/tr}}
        {{if "dPcabinet PriseRDV display_practitioner_name_future_rdv"|gconf && ($contexte|instanceof:'Ox\Mediboard\Mediusers\CMediusers')}}
          {{tr var1=$_consult->_ref_chir}}common-with %s{{/tr}}
        {{elseif $contexte|instanceof:'Ox\Mediboard\Mediusers\CFunctions'}}
          {{tr var1=$_consult->_ref_chir}}common-with %s{{/tr}}
        {{/if}}
        {{if $_consult->docs_necessaires}}
          <br /> <br />
          {{tr}}CConsultation-docs_necessaires{{/tr}} :
          {{mb_value object=$_consult field=docs_necessaires}}
        {{/if}}
      </li>
    {{foreachelse}}
      {{tr}}CConsultation.none{{/tr}}
    {{/foreach}}
    </ol>
  </body>
</html>
