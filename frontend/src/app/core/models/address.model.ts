export interface Address {
  id: number;
  label: string | null;
  street: string;
  city: string;
  province: string;
  postal_code: string;
  phone: string;
  is_default: boolean;
}

export interface AddressPayload {
  label: string | null;
  street: string;
  city: string;
  province: string;
  postal_code: string;
  phone: string;
  is_default: boolean;
}
