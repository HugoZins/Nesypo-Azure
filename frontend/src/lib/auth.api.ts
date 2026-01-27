import {api} from "@/lib/api";

export const register = async (email: string, password: string, passwordConfirm: string) => {
    return await api
        .post("api/register", {
            json: {email, password, passwordConfirm},
        })
        .json();
};

export const login = async (email: string, password: string) => {
    return await api.post("api/login", {
        json: {email, password},
    }).json<any>();
};
