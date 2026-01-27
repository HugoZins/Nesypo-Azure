import {LogoutAlert} from "@/components/auth/LogoutAlert";

export default function DashboardLayout({
                                            children,
                                        }: {
    children: React.ReactNode;
}) {
    return (
        <div className="min-h-screen bg-background">
            <header className="border-b">
                <div className="mx-auto max-w-7xl px-6 h-16 flex items-center justify-between">
                    <h1 className="text-lg font-bold">Dashboard</h1>

                    <nav className="flex items-center gap-6">
                        <a href="/dashboard" className="text-sm hover:underline">
                            Accueil
                        </a>

                        <LogoutAlert />
                    </nav>
                </div>
            </header>

            <main className="mx-auto max-w-7xl px-6 py-6">
                {children}
            </main>
        </div>
    );
}
