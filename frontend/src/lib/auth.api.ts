import { api } from "@/lib/api"

interface LoginResponse {
    token: string
    user: {
        email: string
        roles: string[]
    }
}

export const login = (email: string, password: string) =>
    api
        .post("api/login", {
            json: { email, password },
        })
        .json<LoginResponse>()
