export type AuthMode = 'login' | 'register';

export type SessionUser = {
  email: string;
  id: number;
  name: string;
};

export type AuthSessionResponse = {
  authenticated: boolean;
  csrfToken: string;
  user: SessionUser | null;
};

export type LoginPayload = {
  email: string;
  password: string;
  remember: boolean;
};

export type RegisterPayload = {
  email: string;
  heroName: string;
  password: string;
};
