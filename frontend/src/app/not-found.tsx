import Link from "next/link"
import { Button } from "@/components/ui/button"

export default function NotFound() {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center gap-4">
            <p className="text-6xl font-medium text-muted-foreground">404</p>
            <h1 className="text-xl font-medium">Page introuvable</h1>
            <p className="text-sm text-muted-foreground">
                Cette page n'existe pas ou a été déplacée.
            </p>
            <Button asChild variant="outline">
                <Link href="/dashboard">Retour au dashboard</Link>
            </Button>
        </div>
    )
}