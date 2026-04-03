"use client"

import { useRouter } from "next/navigation"
import { useEffect } from "react"
import { api } from "@/lib/api"
import { useAuthStore } from "@/stores/useAuthStore"
import type { Me } from "@/types/todo"

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
	const router = useRouter()
	const { isAuthenticated, setAuth } = useAuthStore()

	useEffect(() => {
		if (!isAuthenticated) {
			api
				.get("api/me")
				.json<Me>()
				.then((me) => setAuth(me))
				.catch(() => router.push("/login"))
		}
	}, [isAuthenticated, setAuth, router])

	return (
		<div className="min-h-screen bg-background">
			<header className="sticky top-0 z-50 border-b bg-background">
				<div className="mx-auto flex max-w-screen-xl items-center justify-between px-8 py-4">
					<span className="font-bold text-lg">Nesypo</span>
					{/* navbar */}
				</div>
			</header>
			<main className="mx-auto max-w-screen-xl px-8 py-8">{children}</main>
		</div>
	)
}
