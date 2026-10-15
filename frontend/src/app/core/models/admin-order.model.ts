import { OrderItem } from './order.model';

export interface AdminOrderCustomer {
  id: number;
  name: string;
  email: string;
  phone: string | null;
}

export interface AdminOrder {
  id: number;
  status: string;
  total: number;
  discount: number;
  coupon_code: string | null;
  created_at: string;
  items_count?: number;
  customer: AdminOrderCustomer;
  shipping: {
    label: string | null;
    street: string;
    city: string;
    province: string;
    postal_code: string;
    phone: string;
  };
  items?: OrderItem[];
}
