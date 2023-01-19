<?xml version="1.0" encoding="UTF-8"?>
<!-- 
    authenticatorName.sch :
    Contenu :
        Contrôle du nom d'un valideur du document 
    Paramètres d'appel :
        néant
    Historique :
        05/06/11 : FMY ASIP/PRAS : Création
-->
<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="authenticatorName" is-a="personName">
    <p>Conformité du nom d'un approbateur (ou valideur)</p>
    <param name="elt" value="cda:authenticator/cda:assignedEntity/cda:assignedPerson/cda:name"/>
    <param name="vue_elt" value="'authenticator/assignedEntity/assignedPerson/name'"/>
    <param name="nullFlavor" value="1"/>
</pattern>