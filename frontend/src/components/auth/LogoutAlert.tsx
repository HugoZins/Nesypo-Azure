"use client"

import {
	AlertDialog,
	AlertDialogAction,
	AlertDialogCancel,
	AlertDialogContent,
	AlertDialogDescription,
	AlertDialogFooter,
	AlertDialogHeader,
	AlertDialogTitle,
	AlertDialogTrigger,
} from "@/components/ui/alert-dialog"
import { Button } from "@/components/ui/button"
import { useLogout } from "@/hooks/auth/useLogout"

export function LogoutAlert() {
	const logout = useLogout()

	return (
		<AlertDialog>
			<AlertDialogTrigger asChild>
				<Button variant="outline">Déconnexion</Button>
			</AlertDialogTrigger>

			<AlertDialogContent>
				<AlertDialogHeader>
					<AlertDialogTitle>Se déconnecter ?</AlertDialogTitle>
					<AlertDialogDescription>Vous allez être déconnecté de votre compte.</AlertDialogDescription>
				</AlertDialogHeader>

				<AlertDialogFooter>
					<AlertDialogCancel>Annuler</AlertDialogCancel>
					<AlertDialogAction onClick={() => logout.mutate()} className="bg-red-600 text-white">
						Se déconnecter
					</AlertDialogAction>
				</AlertDialogFooter>
			</AlertDialogContent>
		</AlertDialog>
	)
}
