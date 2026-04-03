import Link from "next/link"
import { Button } from "@/components/ui/button"

export default function TodoListNotFound() {
	return (
		<div className="flex h-64 flex-col items-center justify-center gap-4">
			<h2 className="font-medium text-lg">Liste introuvable</h2>
			<p className="text-muted-foreground text-sm">Cette liste n'existe pas ou vous n'y avez pas accès.</p>
			<Button asChild variant="outline">
				<Link href="/dashboard">Retour aux listes</Link>
			</Button>
		</div>
	)
}
