<?xml version="1.0" encoding="UTF-8"?>

<!--                  -=<<o#%@O[ CS24ModeleEnTete.sch ]O@%#o>>=-
    
    Règles de contrôle de l'en-tête CDA  d'un certificat CS24
    
    Historique :
    25/07/11 : CRI : Création
    
-->

<pattern xmlns="http://purl.oclc.org/dsdl/schematron" id="CS24ModeleEnTete">
    <p>Conformité de l'en-tête CDA au modèle du CS24</p>
    <rule context="cda:ClinicalDocument">
        <assert test="./cda:templateId[@root='1.2.250.1.213.1.1.1.5.3']"> 
            Erreur de conformité CS24 :
            L'élément ClinicalDocument/templateId doit être présent 
            avec @root="1.2.250.1.213.1.1.1.5.3".</assert>
        
        <assert test="cda:templateId[@root='1.2.250.1.213.1.1.1.5']"> 
            Erreur: Le template parent "Certificat de Santé de l'Enfant" (1.2.250.1.213.1.1.1.5) doit être présent.
        </assert>
        
        <assert test="./cda:code[@code='CERT_DECL' and @codeSystem='1.2.250.1.213.1.1.4.12']"> 
            Erreur de conformité CS24 : 
            L'élément code doit avoir @code ="CERT_DECL" et @codeSystem = "1.2.250.1.213.1.1.4.12"/>. </assert>
    </rule>
</pattern>
