@extends('layouts.base')

@section('title', 'Términos y Condiciones')

@section('content')
    <div class="prose max-w-none">
        <h1>Términos y Condiciones</h1>

        <p>Última actualización: {{ date('d F, Y') }}</p>

        <h2>1. Aceptación de los Términos</h2>
        <p>Al acceder y utilizar nuestra plataforma de inteligencia artificial para el análisis de documentos legales, usted acepta estar sujeto a estos Términos y Condiciones y a nuestra Política de Privacidad. Si no está de acuerdo con alguno de estos términos, tiene prohibido utilizar o acceder a este sitio.</p>

        <h2>2. Descripción del Servicio</h2>
        <p>Nuestra plataforma utiliza modelos de inteligencia artificial para analizar documentos legales subidos por los usuarios. Los servicios incluyen, entre otros, la generación de resúmenes, la extracción de entidades clave y la respuesta a preguntas específicas sobre el contenido de los documentos.</p>

        <h2>3. Uso Aceptable</h2>
        <p>Usted se compromete a no utilizar la plataforma para ningún propósito ilegal o prohibido por estos términos. No debe utilizar la plataforma de ninguna manera que pueda dañar, deshabilitar, sobrecargar o perjudicar la plataforma o interferir con el uso y disfrute de la plataforma por parte de terceros.</p>

        <h2>4. Cuentas de Usuario</h2>
        <p>Para acceder a ciertas funciones de la plataforma, es posible que deba registrarse para obtener una cuenta. Usted es responsable de mantener la confidencialidad de la información de su cuenta, incluida su contraseña, y de todas las actividades que ocurran bajo su cuenta.</p>

        <h2>5. Propiedad Intelectual</h2>
        <p>El contenido que usted sube a la plataforma sigue siendo de su propiedad. Sin embargo, al subir contenido, nos otorga una licencia mundial, no exclusiva, libre de regalías, para usar, reproducir, modificar y procesar dicho contenido con el único propósito de proporcionarle los servicios solicitados.</p>

        <h2>6. Limitación de Responsabilidad</h2>
        <p>La plataforma y sus servicios se proporcionan "tal cual". No garantizamos que los análisis generados por la IA sean 100% precisos o completos. Usted es el único responsable de revisar y validar la información proporcionada por la plataforma antes de tomar cualquier decisión legal o comercial basada en ella.</p>

        <h2>7. Cambios en los Términos</h2>
        <p>Nos reservamos el derecho de modificar estos términos en cualquier momento. Le notificaremos cualquier cambio publicando los nuevos Términos y Condiciones en esta página. Se le aconseja que revise estos Términos y Condiciones periódicamente para detectar cualquier cambio.</p>

        <h2>8. Contacto</h2>
        <p>Si tiene alguna pregunta sobre estos Términos y Condiciones, puede contactarnos a través de nuestro formulario de contacto.</p>
    </div>

    <div class="mt-8 text-center">
        <button onclick="window.history.back()" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-900 transition-colors">
            Regresar
        </button>
    </div>
@endsection