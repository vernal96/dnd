export type AuthMode = 'login' | 'register';
export type AuthView = 'forgot-password' | 'login' | 'register';

export type SessionUser = {
    canAccessGm: boolean;
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
    login: string;
    password: string;
    remember: boolean;
};

export type RegisterPayload = {
    email: string;
    login: string;
    password: string;
};

export type ForgotPasswordPayload = {
    email: string;
};

export type ResetPasswordPayload = {
    email: string;
    password: string;
    passwordConfirmation: string;
    token: string;
};
