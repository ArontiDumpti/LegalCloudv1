<?php

namespace App\Support;

class DocumentosDemo
{
    public static function reglamentoTramites(): string
    {
        return <<<'TXT'
RESOLUCIÓN N.º 187-2024
REGLAMENTO GENERAL DE TRÁMITES ADMINISTRATIVOS

La Dirección General de Gestión Documental, en uso de sus facultades legales,

CONSIDERANDO:
Que es necesario unificar los requisitos, plazos y responsables para la tramitación de procedimientos administrativos de la entidad.

RESUELVE:

Artículo 1. Objeto.
El presente reglamento establece las reglas para iniciar, tramitar y resolver procedimientos administrativos internos y externos, así como los requisitos documentales exigibles a los interesados.

Artículo 2. Ámbito de aplicación.
Aplica a todas las dependencias, servidores públicos y personas naturales o jurídicas que presenten solicitudes ante la entidad.

Artículo 8. Firma de la solicitud.
La solicitud podrá ser firmada por:
1. El interesado, de manera manuscrita o con firma electrónica avanzada.
2. Un apoderado, siempre que acredite poder especial o general suficiente.
3. El representante legal de una persona jurídica, con certificado de existencia y representación legal vigente no mayor a treinta (30) días.

Artículo 12. Requisitos para iniciar un procedimiento administrativo.
Para realizar un procedimiento administrativo ante la entidad, el interesado deberá presentar, de manera completa, los siguientes requisitos:
1. Solicitud escrita, física o digital, dirigida a la dependencia competente, con identificación del trámite solicitado.
2. Copia del documento de identidad del interesado o, en caso de persona jurídica, certificado de existencia y representación legal.
3. Formulario oficial del trámite, debidamente diligenciado y firmado.
4. Soportes que acrediten los hechos o derechos invocados, en formato PDF o imagen legible.
5. Comprobante de pago de la tasa administrativa, cuando el trámite lo exija.
6. Poder o autorización, si actúa un tercero en nombre del interesado.
7. Dirección física y correo electrónico para notificaciones.

Parágrafo. Si la solicitud se presenta de forma incompleta, la entidad requerirá al interesado para que subsane dentro de los cinco (5) días hábiles siguientes. Vencido el término sin respuesta, se entenderá desistida.

Artículo 15. Clasificación y expediente.
Toda solicitud dará lugar a un expediente electrónico. Los documentos se clasificarán por categoría, se versionarán y quedarán sujetos a control de acceso según el rol del usuario.

Artículo 18. Plazos para la presentación de recursos.
Los recursos de reposición y apelación deberán interponerse dentro de los diez (10) días hábiles siguientes a la notificación del acto. El recurso de queja procederá dentro de los cinco (5) días hábiles siguientes al rechazo o no concesión del de apelación.

Artículo 21. Conservación y trazabilidad.
Los documentos, consultas y operaciones realizadas por los usuarios se registrarán en la bitácora de auditoría. El tiempo mínimo de conservación del expediente es de diez (10) años.

Comuníquese y cúmplase.
Dada a los doce (12) días del mes de marzo de 2024.
TXT;
    }

    public static function resolucionPlazos(): string
    {
        return <<<'TXT'
RESOLUCIÓN N.º 042-2025
DIRECTRICES COMPLEMENTARIAS SOBRE PLAZOS, NOTIFICACIONES Y SUBSANACIÓN

La Oficina Jurídica,

CONSIDERANDO:
Que el Reglamento General de Trámites Administrativos (Resolución 187-2024) requiere precisiones operativas para su aplicación uniforme.

RESUELVE:

Artículo 3. Cómputo de plazos.
Los plazos se contarán en días hábiles, excluyendo sábados, domingos y festivos nacionales. El día de la notificación no se computa.

Artículo 4. Notificaciones.
Las notificaciones se realizarán de preferencia al correo electrónico informado en la solicitud. Se entenderán surtidas al día hábil siguiente al envío, si no existe prueba de rechazo del mensaje.

Artículo 5. Subsanación de requisitos.
Cuando falte alguno de los requisitos del artículo 12 de la Resolución 187-2024, el analista documental emitirá un requerimiento único. El interesado tendrá cinco (5) días hábiles para completar la documentación.

Artículo 6. Consulta del acervo.
Los usuarios consultores podrán preguntar en lenguaje natural sobre reglamentos y resoluciones vigentes. Las respuestas del asistente deberán mostrar las fuentes documentales que las sustentan. Este módulo es demostrativo y no sustituye el criterio jurídico oficial.

Artículo 7. Vigencia.
La presente resolución rige a partir de su publicación y complementa, sin derogar, la Resolución 187-2024.

        Publíquese.
Dada a los ocho (8) días del mes de enero de 2025.
TXT;
    }

    public static function circularClasificacion(): string
    {
        return <<<'TXT'
CIRCULAR N.º 08-2026
CLASIFICACIÓN, VERSIONADO Y CARGA DE DOCUMENTOS EN LEGALCLOUD

De: Responsable documental
Para: Analistas, especialistas y usuarios consultores

1. Todo documento normativo se cargará en PDF. El sistema extraerá el texto (OCR de demostración) y lo dejará disponible para búsqueda y para el asistente.

2. Las categorías vigentes son: Reglamento, Resolución, Circular, Expediente administrativo y Contrato.

3. Cada carga genera una versión. Los cambios sustanciales deben describirse en el resumen de versión.

4. Los documentos indexados pueden consultarse en lenguaje natural. Ejemplo: "¿Cuáles son los requisitos establecidos para realizar determinado procedimiento?"

5. La bitácora de auditoría registra altas, OCR y consultas.

Cumplimiento inmediato.
TXT;
    }

    public static function solicitudLicencia(): string
    {
        return <<<'TXT'
FORMULARIO DE SOLICITUD DE LICENCIA DE FUNCIONAMIENTO
Expediente EXP-2026-0041 — Cooperativa Andes

Solicitante: Cooperativa Andes
NIT: 900.441.228-4
Representante legal: Marta Ríos
Correo de notificación: legal@andes.coop

Trámite solicitado: Licencia de funcionamiento para sede administrativa.

Documentos anexos:
1. Solicitud escrita.
2. Certificado de existencia y representación legal.
3. Formulario oficial diligenciado.
4. Comprobante de pago de la tasa administrativa.

Declaración: La cooperativa manifiesta que la información es veraz y se sujeta al Reglamento General de Trámites Administrativos (Resolución 187-2024).
TXT;
    }

    public static function contratoSoporte(): string
    {
        return <<<'TXT'
CONTRATO DE PRESTACIÓN DE SERVICIOS DE GESTIÓN DOCUMENTAL
N.º CT-2026-015

Entre LegalCloud AI y la entidad contratante se acuerda:

Cláusula 1. Objeto.
Proveer la plataforma de expediente electrónico, versionado, búsqueda y consulta asistida sobre el acervo documental.

Cláusula 2. Entregables.
Repositorio de documentos en PDF, bitácora de auditoría y módulo de preguntas con fuentes.

Cláusula 3. Confidencialidad.
Los documentos jurídicos y administrativos se tratan según el rol del usuario: administrador, responsable documental, especialista y consultor.

Cláusula 4. Vigencia.
Un (1) año, prorrogable de común acuerdo.
TXT;
    }
}
