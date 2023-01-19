<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    relatedPersonName.sch :
    Contenu :
        Contrôle du nom d'un proche du patient 
    Paramètres d'appel :
        néant
    Historique :
        05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="relatedPersonName" is-a="personName">
    <p>Conformité du nom d'un proche du patient, nullFlavor autorisé</p>
    <param name="elt" value="cda:relatedPerson/cda:name"/>
    <param name="vue_elt" value="'relatedPerson/name'"/>
    <param name="nullFlavor" value="1"/>
</pattern>