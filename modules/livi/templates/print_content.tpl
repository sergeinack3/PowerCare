{{*
 * @package Mediboard\Livi
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
*}}

<html>
<head>
  <style>
    {{$css_content|smarty:nodefaults}}
  </style>
</head>
<body>
<style>
  @media print {
    #print_pdf fieldset > legend {
      width: 99%;
    }

    #print_pdf > table.main > tbody > tr > td > fieldset:not([class^='v-']) {
      padding: 22px 0 0 0 !important;
      margin: 0 !important;
      border: 0 !important;
    }

    #print_pdf fieldset {
      padding: 0 !important;
      border-width: 0 !important;
    }

    #print_pdf table.tbl {
      width: 100%;
    }

    #print_pdf table.form {
      margin: 0 !important;
      width: 100% !important;
    }

    #print_pdf table.tbl > tbody > tr > th,
    #print_pdf table.tbl > tbody > tr > td {
      border-top: none !important;
      border-left: none !important;
    }

    #print_pdf table.tbl > tbody > tr > th:last-of-type,
    #print_pdf table.tbl > tbody > tr > td:last-of-type {
      border-right: none !important;
    }

    #print_pdf table.tbl > tbody > tr:last-of-type td {
      border-bottom: none !important;
    }

    #print_pdf table.tbl > tbody > tr > th.me-border-top {
      border-top: solid 2px #CFD8DC !important;
    }

    body fieldset > legend {
      width: 99%;
    }

    #print_pdf table.tbl {
      width: 100%;
    }

    #print_pdf table.tbl > tbody > tr > th,
    #print_pdf table.tbl > tbody > tr > td {
      border-top: none !important;
      border-left: none !important;
    }

    #print_pdf table.tbl > tbody > tr > th:last-of-type,
    #print_pdf table.tbl > tbody > tr > td:last-of-type {
      border-right: none !important;
    }

    #print_pdf table.tbl > tbody > tr:last-of-type td {
      border-bottom: none !important;
    }

    #print_pdf table.tbl > tbody > tr > th.me-border-top {
      border-top: solid 2px #CFD8DC !important;
    }
  }
</style>
<div id='print_pdf'>{{$content|smarty:nodefaults}}</div>
</body>
</html>
