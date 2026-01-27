import ky from "ky";

export const api = ky.create({
    prefixUrl: "http://localhost:8000",
    credentials: "include",   // très important
    headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
    },
});

