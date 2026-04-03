import Link from "next/link"
import { Button } from "@/components/ui/button"

export default function NotFound() {
	return (
		<div className="flex min-h-screen flex-col items-center justify-center gap-4">
			<p className="font-medium text-6xl text-muted-foreground">404</p>
			<h1 className="font-medium text-xl">Page introuvable</h1>
			<p className="text-muted-foreground text-sm">Cette page n'existe pas ou a été déplacée.</p>
			<Button asChild variant="outline">
				<Link href="/dashboard">Retour au dashboard</Link>
			</Button>
		</div>
	)
}
