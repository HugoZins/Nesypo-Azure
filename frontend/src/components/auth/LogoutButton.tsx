"use client"

import { logout } from "@/lib/authApi"

export default function LogoutButton() {
	const handleLogout = async () => {
		try {
			await logout()
		} finally {
			// Le middleware prendra le relais
			window.location.href = "/login"
		}
	}

	return (
		<button
			type="button"
			onClick={handleLogout}
			className="text-red-600 text-sm hover:underline"
		>
			Se déconnecter
		</button>

	)
}
