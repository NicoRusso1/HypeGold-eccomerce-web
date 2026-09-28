export interface User {
  id: number;
  name: string;
  email: string;
  phone: string | null;
  role: 'cliente' | 'administrador';
}

export interface AuthResponse {
  user: User;
  token: string;
}
