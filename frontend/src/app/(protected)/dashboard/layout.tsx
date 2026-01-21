import { Sidebar, SidebarProvider } from "@/components/ui/sidebar";

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
    return (
        <SidebarProvider>
            <div className="min-h-screen flex bg-background">
                <Sidebar className="w-72">
                    <div className="p-4">
                        <h2 className="text-lg font-bold">Menu</h2>
                    </div>
                    <div className="p-2 space-y-1">
                        <a className="block px-3 py-2 rounded-md hover:bg-accent" href="/dashboard">
                            Dashboard
                        </a>
                        <a className="block px-3 py-2 rounded-md hover:bg-accent" href="/dashboard/todo-lists">
                            TodoLists
                        </a>
                    </div>
                </Sidebar>

                <div className="flex-1">
                    <header className="flex items-center justify-between p-6 border-b">
                        <h1 className="text-2xl font-bold">Dashboard</h1>
                    </header>

                    <main className="p-6">{children}</main>
                </div>
            </div>
        </SidebarProvider>
    );
}
