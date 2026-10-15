export interface AdminReview {
  id: number;
  author_name: string;
  rating: number;
  comment: string | null;
  created_at: string;
  product: {
    id: number;
    name: string;
  };
}

export interface ReviewPayload {
  product_id: number;
  author_name: string;
  rating: number;
  comment: string | null;
}
