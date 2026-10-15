import { Category } from './category.model';

export interface AdminProductVariant {
  id?: number;
  talle: string | null;
  sku: string;
  stock: number;
  extra_price: number;
}

export interface AdminProductImage {
  id: number;
  url: string;
  order: number;
}

export interface AdminProduct {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  material: string;
  base_price: number;
  active: boolean;
  category: Category | null;
  variants?: AdminProductVariant[];
  images?: AdminProductImage[];
}

export interface AdminProductPayload {
  category_id: number;
  name: string;
  description: string | null;
  material: string;
  base_price: number;
  active: boolean;
  variants: AdminProductVariant[];
}
