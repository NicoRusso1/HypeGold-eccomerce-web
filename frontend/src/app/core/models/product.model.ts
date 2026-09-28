import { Category } from './category.model';

export interface Product {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  material: string;
  base_price: number;
  total_stock: number;
  image: string | null;
  category: Category | null;
}

export type ProductSort = 'recientes' | 'precio_asc' | 'precio_desc' | 'nombre';

export interface ProductFilters {
  search?: string;
  category?: string;
  material?: string;
  min_price?: number;
  max_price?: number;
  sort?: ProductSort;
  per_page?: number;
  page?: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}
