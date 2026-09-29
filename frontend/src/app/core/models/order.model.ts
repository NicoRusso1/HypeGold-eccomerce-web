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
  discount: number;
  coupon_code: string | null;
  created_at: string;
  shipping: {
    label: string | null;
    street: string;
    city: string;
    province: string;
    postal_code: string;
    phone: string;
  };
  items?: OrderItem[];
  items_count?: number;
}

export interface CouponValidation {
  code: string;
  type: 'percentage' | 'fixed';
  value: number;
  subtotal: number;
  discount: number;
  total: number;
}
