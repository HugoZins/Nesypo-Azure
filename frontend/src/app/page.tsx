"use client";

import {useEffect, useState} from "react";
import {useRouter} from "next/navigation";
import {api} from "@/lib/api";

export default function HomePage() {
    const router = useRouter();
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        async function checkAuth() {
            try {
                await api.get("api/me").json();
                router.push("/dashboard");
            } catch (e) {
                router.push("/login");
            } finally {
                setLoading(false);
            }
        }

        checkAuth();
    }, [router]);

    return <div>{loading ? "Vérification..." : "Redirection..."}</div>;
}
