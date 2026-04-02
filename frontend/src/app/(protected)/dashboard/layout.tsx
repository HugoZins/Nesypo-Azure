import { LogoutAlert } from "@/components/auth/LogoutAlert"
import React from "react";

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
	return (
		<div className="min-h-screen bg-background">
			<header className="sticky top-0 z-10 border-b border-border bg-card">
				<div className="mx-auto flex h-14 max-w-screen-xl items-center justify-between px-8">
					<h1 className="text-sm font-medium tracking-wide text-foreground">
						Nesypo
					</h1>
					<nav className="flex items-center gap-6">
						<a href="/dashboard" className="text-sm text-muted-foreground hover:text-foreground transition-colors">
							Accueil
						</a>
						<LogoutAlert />
					</nav>
				</div>
			</header>
			<main className="mx-auto max-w-screen-xl px-8 py-8">{children}</main>
		</div>
	)
}