import {create} from "zustand";

interface AuthState {
    isAuthenticated: boolean;
    setAuthenticated: (value: boolean) => void;
    logout: () => void;
}

export const useAuthStore = create<AuthState>((set) => ({
    isAuthenticated: true, // par défaut (token existant)
    setAuthenticated: (value) => set({isAuthenticated: value}),
    logout: () => {
        set({isAuthenticated: false});
    },
}));
