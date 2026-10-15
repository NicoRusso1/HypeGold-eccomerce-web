export interface AdminStockItem {
  id: number;
  talle: string | null;
  sku: string;
  stock: number;
  low_stock: boolean;
  product: {
    id: number;
    name: string;
    category: string | null;
  };
}

export interface AdminStockMeta {
  low_stock_threshold: number;
  low_stock_count: number;
}
