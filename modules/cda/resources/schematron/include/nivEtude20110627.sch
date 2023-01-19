<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    nivEtude.sch :
    Contenu :
    Contrôle du code du niveau d'études d'une personne de la section Coded Social history  (nullFlavor interdit)
        Spécialisation du pattern dansJeuDeValeurs
    Paramètres d'appel :
        Néant 
    Historique :
        27/06/11 : CRI ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="nivEtude" is-a="dansJeuDeValeurs">
    <p>Conformité du niveau d'études de la personne au CI-SIS</p>
    <param name="path_jdv" value="$jdv_nivEtude"/>
    <param name="vue_elt" value="'ClinicalDocument/author/assignedAuthor/code'"/>
    <param name="xpath_elt" value="/cda:ClinicalDocument/cda:component/cda:structuredBody/cda:component/cda:section/cda:entry/cda:observation[cda:templateId/@root='1.3.6.1.4.1.19376.1.5.3.1.4.13.4' and cda:code/@code='S-00610']/cda:value"/>
    <param name="nullFlavor" value="0"/>
</pattern>   
