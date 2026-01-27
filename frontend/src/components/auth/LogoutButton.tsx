"use client";

import {logout} from "@/lib/authApi";

export default function LogoutButton() {
    const handleLogout = async () => {
        try {
            await logout();
        } finally {
            // Le middleware prendra le relais
            window.location.href = "/login";
        }
    };

    return (
        <button
            onClick={handleLogout}
            className="text-sm text-red-600 hover:underline"
        >
            Se déconnecter
        </button>
    );
}
