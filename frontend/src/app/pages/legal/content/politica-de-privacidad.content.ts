import { LegalPageContent } from '../legal-content.model';

export const POLITICA_DE_PRIVACIDAD: LegalPageContent = {
  title: 'Política de privacidad',
  updatedAt: 'octubre de 2026',
  intro:
    'En HypeGold Joyas respetamos tu privacidad. Esta política explica qué datos personales recopilamos, para qué los usamos y qué derechos tenés sobre ellos, en línea con la Ley 25.326 de Protección de los Datos Personales.',
  sections: [
    {
      heading: 'Qué datos recopilamos',
      paragraphs: ['Cuando creás una cuenta, comprás un producto o nos contactás, podemos recopilar:'],
      list: [
        'Nombre y apellido',
        'Email y teléfono',
        'Dirección de envío',
        'Historial de pedidos y compras',
      ],
    },
    {
      heading: 'Para qué usamos tus datos',
      paragraphs: ['Usamos tus datos únicamente para:'],
      list: [
        'Procesar y entregar tus pedidos',
        'Comunicarnos con vos sobre el estado de tu compra',
        'Gestionar tu cuenta y tus direcciones guardadas',
        'Responder tus consultas',
      ],
    },
    {
      heading: 'Con quién compartimos tus datos',
      paragraphs: [
        'Compartimos los datos estrictamente necesarios con Mercado Pago para procesar los pagos, y con el correo o empresa de logística que realiza el envío de tu pedido. No vendemos ni cedemos tus datos a terceros con fines publicitarios.',
      ],
    },
    {
      heading: 'Tus derechos',
      paragraphs: [
        'Como titular de tus datos personales, tenés derecho a acceder, rectificar y solicitar la supresión de tu información. Podés ejercer estos derechos escribiéndonos por los medios de contacto que figuran al final de esta página.',
        'La Agencia de Acceso a la Información Pública, en su carácter de Órgano de Control de la Ley 25.326, tiene la atribución de atender las denuncias y reclamos que interpongan quienes resulten afectados en sus derechos por incumplimiento de las normas vigentes.',
      ],
    },
    {
      heading: 'Cookies',
      paragraphs: [
        'El sitio utiliza almacenamiento local del navegador para mantener tu sesión iniciada y recordar el contenido de tu carrito de compras.',
      ],
    },
    {
      heading: 'Contacto',
      paragraphs: [
        'Para cualquier consulta sobre el tratamiento de tus datos, escribinos por WhatsApp al +54 9 345 404 0162 o por Instagram a @hypegold.arg.',
      ],
    },
  ],
};
