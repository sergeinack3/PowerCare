<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    BIOserviceCode.sch :
    Contenu :
        Contrôle du documentationOf/serviceEvent/code dans l'en-tête CDA d'un CR d'examens biologiques 
        Spécialisation du pattern dansJeuDeValeurs
    Paramètres d'appel :
        Néant 
    Historique :
        21/07/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="BIOserviceCode" is-a="dansJeuDeValeurs">
    <p>Conformité du code acte documenté dans un CR d'examens biologiques</p>
    <param name="path_jdv" value="$jdv_chapitresBiologie"/>
    <param name="vue_elt" value="'ClinicalDocument/documentationOf/serviceEvent/code'"/>
    <param name="xpath_elt" value="cda:ClinicalDocument/cda:documentationOf/cda:serviceEvent/cda:code"/>
    <param name="nullFlavor" value="1"/>
</pattern>   
