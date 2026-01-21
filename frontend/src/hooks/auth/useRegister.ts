import { useMutation } from "@tanstack/react-query";
import { register } from "@/lib/auth.api";

export function useRegister() {
    return useMutation({
        mutationFn: ({
                         email,
                         password,
                         passwordConfirm,
                     }: {
            email: string;
            password: string;
            passwordConfirm: string;
        }) => register(email, password, passwordConfirm),
    });
}
