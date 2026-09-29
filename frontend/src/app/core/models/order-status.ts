/** Pasos de la línea de tiempo normal de un pedido (HG-29). "cancelado" es un estado aparte. */
export const ORDER_STATUS_STEPS = ['pendiente', 'pagado', 'enviado', 'entregado'] as const;

export const ORDER_STATUS_LABELS: Record<string, string> = {
  pendiente: 'Pendiente',
  pagado: 'Pagado',
  enviado: 'Enviado',
  entregado: 'Entregado',
  cancelado: 'Cancelado',
};

export function ordenStatusLabel(status: string): string {
  return ORDER_STATUS_LABELS[status] ?? status;
}
