export interface CartItem {
  id: number;
  quantity: number;
  unit_price: number;
  subtotal: number;
  variant: {
    id: number;
    talle: string | null;
    stock: number;
  };
  product: {
    id: number;
    name: string;
    slug: string;
    image: string | null;
  };
}

export interface Cart {
  id: number;
  items: CartItem[];
  items_count: number;
  total: number;
}
