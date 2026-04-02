import type { Metadata } from "next"
import "./globals.css"
import { Toaster } from "@/components/ui/sonner"
import { QueryProvider } from "@/providers/QueryProvider"

export const metadata: Metadata = {
	title: {
		default: "Nesypo",
		template: "%s | Nesypo",
	},
}

export default function RootLayout({ children }: { children: React.ReactNode }) {
	return (
		<html lang="fr">
		<body className="min-h-screen bg-background text-foreground antialiased">
		<QueryProvider>{children}</QueryProvider>
		<Toaster richColors position="bottom-right" />
		</body>
		</html>
	)
}