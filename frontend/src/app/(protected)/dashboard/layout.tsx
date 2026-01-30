import { LogoutAlert } from "@/components/auth/LogoutAlert"

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
	return (
		<div className="min-h-screen bg-background">
			<header className="border-b">
				<div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-6">
					<h1 className="font-bold text-lg">Dashboard</h1>

					<nav className="flex items-center gap-6">
						<a href="/dashboard" className="text-sm hover:underline">
							Accueil
						</a>

						<LogoutAlert />
					</nav>
				</div>
			</header>

			<main className="mx-auto max-w-7xl px-6 py-6">{children}</main>
		</div>
	)
}
