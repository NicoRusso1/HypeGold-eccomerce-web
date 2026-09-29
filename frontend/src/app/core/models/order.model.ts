export interface OrderItem {
  id: number;
  product_name: string;
  variant_talle: string | null;
  sku: string;
  unit_price: number;
  quantity: number;
  subtotal: number;
}

export interface Order {
  id: number;
  status: string;
  total: number;
  created_at: string;
  shipping: {
    label: string | null;
    street: string;
    city: string;
    province: string;
    postal_code: string;
    phone: string;
  };
  items: OrderItem[];
}
