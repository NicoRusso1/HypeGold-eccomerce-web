import { LegalPageContent } from '../legal-content.model';

export const POLITICA_DE_CAMBIOS: LegalPageContent = {
  title: 'Política de cambios y devoluciones',
  updatedAt: 'octubre de 2026',
  intro: 'Queremos que estés conforme con tu compra. Así funcionan los cambios y devoluciones en HypeGold Joyas.',
  sections: [
    {
      heading: 'Derecho de arrepentimiento',
      paragraphs: [
        'Tenés 10 días corridos desde que recibís tu pedido para arrepentirte de la compra, sin necesidad de dar ningún motivo, según lo establece la Ley 24.240 de Defensa del Consumidor.',
      ],
    },
    {
      heading: 'Condiciones para el cambio o devolución',
      paragraphs: ['Para aceptar un cambio o devolución, el producto debe estar:'],
      list: [
        'Sin uso y en las mismas condiciones en que fue entregado',
        'Con su packaging original',
        'Acompañado del comprobante de compra',
      ],
    },
    {
      heading: 'Cómo solicitar un cambio',
      paragraphs: [
        'Escribinos por WhatsApp al +54 9 345 404 0162 contándonos el número de pedido y el motivo del cambio o devolución. Te vamos a indicar los pasos a seguir.',
      ],
    },
    {
      heading: 'Costos de envío',
      paragraphs: [
        'Si el cambio es por arrepentimiento, el costo del envío de vuelta corre por cuenta del cliente. Si el producto llegó con una falla, el costo del envío lo cubre HypeGold Joyas.',
      ],
    },
    {
      heading: 'Reintegro',
      paragraphs: [
        'Una vez que recibimos y revisamos el producto devuelto, procesamos el reintegro por el mismo medio de pago utilizado en la compra, dentro de los 10 días hábiles.',
      ],
    },
  ],
};
