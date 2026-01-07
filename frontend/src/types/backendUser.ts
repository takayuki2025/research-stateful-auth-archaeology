export interface BackendUser {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;

  uid: string;

  post_number: string | null;
  address: string | null;
  building: string | null;

  user_image?: string | null;
}
