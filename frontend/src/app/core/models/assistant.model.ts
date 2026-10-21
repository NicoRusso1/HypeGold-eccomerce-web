export interface AssistantTurn {
  role: 'user' | 'assistant';
  content: string;
}

export interface AssistantProduct {
  id: number;
  name: string;
  slug: string;
  base_price: number;
  image: string | null;
  variant_id: number | null;
}

export interface AssistantChatResponse {
  reply: string;
  products: AssistantProduct[];
}
