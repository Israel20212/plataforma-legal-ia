@extends('layouts.base')

@section('title', 'Aviso de Privacidad')

@section('content')
    <div class="prose max-w-none">
        <h1>Aviso de Privacidad</h1>

        <p>Última actualización: {{ date('d F, Y') }}</p>

        <h2>1. Responsable de los Datos Personales</h2>
        <p>LexIA, con domicilio en [Tu Dirección], es responsable del tratamiento de sus datos personales.</p>

        <h2>2. Datos que Recopilamos</h2>
        <p>Recopilamos los siguientes datos personales:</p>
        <ul>
            <li>Nombre completo</li>
            <li>Dirección de correo electrónico</li>
            <li>Contraseña (cifrada)</li>
            <li>Documentos legales que usted sube a la plataforma</li>
        </ul>

        <h2>3. Finalidad del Tratamiento de Datos</h2>
        <p>Sus datos personales serán utilizados para las siguientes finalidades:</p>
        <ul>
            <li>Proveer los servicios de análisis de documentos legales.</li>
            <li>Crear y administrar su cuenta de usuario.</li>
            <li>Mejorar la calidad de nuestros servicios de inteligencia artificial.</li>
            <li>Contactarlo para asuntos relacionados con el servicio.</li>
        </ul>

        <h2>4. Confidencialidad y Seguridad</h2>
        <p>Los documentos que usted sube son tratados con estricta confidencialidad. Utilizamos medidas de seguridad técnicas y organizativas para proteger sus datos personales y los documentos contra el acceso no autorizado, la alteración, la divulgación o la destrucción.</p>

        <h2>5. Derechos ARCO</h2>
        <p>Usted tiene derecho a Acceder, Rectificar, Cancelar u Oponerse al tratamiento de sus datos personales. Para ejercer estos derechos, por favor envíe una solicitud a [Tu Correo de Contacto].</p>

        <h2>6. Cambios al Aviso de Privacidad</h2>
        <p>Nos reservamos el derecho de efectuar en cualquier momento modificaciones o actualizaciones al presente aviso de privacidad. Le notificaremos de cualquier cambio a través de la plataforma o por correo electrónico.</p>
    </div>

    <div class="mt-8 text-center">
        <button onclick="window.history.back()" class="bg-gray-800 text-white px-6 py-2 rounded-lg hover:bg-gray-900 transition-colors">
            Regresar
        </button>
    </div>
@endsection