<?php

use Ox\Core\CCanDo;
use Ox\Core\CSmartyDP;
use Ox\Core\CView;

CCanDo::checkEdit();

$data_benef  = CView::post("data_benef", "str");
$data_assure = CView::post("data_assure", "str");
$rank = CView::post("rank", "str");
CView::checkin();

$data_benef = str_replace('\\', '', $data_benef);
$data_benef = utf8_encode($data_benef);
if (substr($data_benef, -1) == '"' && substr($data_benef, 0, 1) == '"') {
  $data_benef = substr($data_benef, 1, -1);
}
$array_benef = json_decode($data_benef, true);

if ($data_assure) {
  $data_assure = str_replace('\\', '', $data_assure);
  $data_assure = utf8_encode($data_assure);
  if (substr($data_assure, -1) == '"' && substr($data_assure, 0, 1) == '"') {
    $data_assure = substr($data_assure, 1, -1);
  }
  $array_assure = json_decode($data_assure, true);
  $array_assure = array("assure" => $array_assure);

  $data = array_merge($array_benef, $array_assure);
}
else {
  $data = $array_benef;
}

foreach ($data as $cat => $_data) {
  foreach ($data[$cat] as $key => $value) {
    if (is_array($value)) {
      if (array_key_exists("raw", $value)) {
        $values[$cat][$key] = array(
          "value"   => utf8_decode($value["raw"] . "-" . $value["chaine"]),
          "checked" => false
        );
      }
      elseif ($key == "adresse") {
        //A Completer
        $adresse = "";
        for ($i = 1; $i <= 5; $i++) {
          if (array_key_exists("ligne" . $i, $value) && $value["ligne" . $i]) {
            $adresse = $adresse . " " . $value["ligne" . $i];
          }
        }
        $values[$cat][$key] = array(
          "value"   => trim($adresse),
          "checked" => false
        );
      }
      elseif (array_key_exists("date", $value)) {
        $date               = $value["date"];
        $values[$cat][$key] = array(
          "value"   => $date,
          "checked" => false
        );
      }
      elseif (array_key_exists("dateEnCarte", $value)) {
        $date               = substr($value["dateEnCarte"], -2) . substr($value["dateEnCarte"], 2, 2) . substr($value["dateEnCarte"], 0, 2);
        $values[$cat][$key] = array(
          "value"   => $date,
          "checked" => false
        );
      }
      else {
        foreach ($value as $_key => $_item) {
          if (is_array($_item)) {
            if (array_key_exists("debut", $_item) && array_key_exists("chaine", $_item)) {
              $date                             = substr_replace($_item["debut"], "/", 2, 0);
              $date                             = substr_replace($date, "/", 5, 0);
              $values[$cat][$key . "-" . $_key] = array(
                "value"   => array(
                  "debut"  => $date,
                  "chaine" => utf8_decode($_item["chaine"])
                ),
                "checked" => true
              );
            }
          }
          else {
            $values[$cat][$key] = array(
              "value"   => trim(utf8_decode($_item)),
              "checked" => false
            );
          }
        }
      }
    }
    else {
      $values[$cat][$key] = array(
        "value"   => trim(utf8_decode($value)),
        "checked" => false
      );
    }
  }
}

//Tableau contenant les dates à formater
$dates = array(
  "ident" => array(
    "dateCertification",
    "naissance",
    "naissance-date"
  ),

  "amc" => array(
    "validiteDonnees-debut",
    "validiteDonnees-fin"
  ),

  "assure" => array(
    "dateCertification",
    "naissance",
    "naissance-date"
  )
);

foreach ($dates as $_cat => $listChamps) {
  if (array_key_exists($_cat, $values)) {
    foreach ($listChamps as $date) {
      if (array_key_exists($date, $values[$_cat])) {
        $_date                         = &$values[$_cat][$date]["value"];
        $values[$_cat][$date]["value"] = substr_replace($values[$_cat][$date]["value"], "/", 2, 0);
        $values[$_cat][$date]["value"] = substr_replace($values[$_cat][$date]["value"], "/", 5, 0);
      }
    }
  }
}

unset($_date);


//Tableau contenant les champs à checker par défaut
$checked = array(
  "ident" => array(
    "prenomUsuel",
    "nirCertifie",
    "adresse",
    "naissance",
    "nomUsuel",
    "rangDeNaissance"
  ),

  "amc" => array(
    "numComplB2",
    "codeRoutageFlux",
    "validiteDonnees",
    "numAdherent",
    "numAdherent",
    "indicTraitement"
  ),

  "amo" => array(
    "codeGestion",
    "medecinTraitant",
    "centreCarte",
    "qualBenef",
    "centreGestion",
    "codeRegime",
    "caisse"
  ),

  "assure" => array(
    "prenomUsuel",
    "nirCertifie",
    "adresse",
    "naissance",
    "nomUsuel"
  )
);

foreach ($checked as $_cat => $listChamps) {
  if (array_key_exists($_cat, $values)) {
    foreach ($listChamps as $champ) {
      if (array_key_exists($champ, $values[$_cat])) {
        $values[$_cat][$champ]["checked"] = true;
      }
    }
  }
}

$smarty = new CSmartyDP();
$smarty->assign("values", $values);
$smarty->assign("rank", $rank);

$smarty->display("inc_vw_choose_infos_vitale.tpl");