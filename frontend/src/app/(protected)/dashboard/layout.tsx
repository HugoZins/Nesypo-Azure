import { Sidebar, SidebarProvider } from "@/components/ui/sidebar";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { Button } from "@/components/ui/button";
import { DropdownMenu, DropdownMenuTrigger, DropdownMenuContent, DropdownMenuItem } from "@/components/ui/dropdown-menu";

export default function DashboardLayout({ children }: { children: React.ReactNode }) {
    return (
        <SidebarProvider>
            <div className="min-h-screen flex bg-background">
                <Sidebar className="w-72">
                    <div className="p-4">
                        <h2 className="text-lg font-bold">Menu</h2>
                    </div>
                    <Separator />
                    <div className="p-2">
                        <a className="block px-3 py-2 rounded-md hover:bg-accent" href="/dashboard">
                            Dashboard
                        </a>
                        <a className="block px-3 py-2 rounded-md hover:bg-accent" href="/dashboard/todos">
                            Todos
                        </a>
                    </div>
                </Sidebar>

                <div className="flex-1">
                    <header className="flex items-center justify-between p-6 border-b">
                        <h1 className="text-2xl font-bold">Dashboard</h1>

                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline">User</Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent>
                                <DropdownMenuItem>Profile</DropdownMenuItem>
                                <DropdownMenuItem>Logout</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </header>

                    <main className="p-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Welcome</CardTitle>
                            </CardHeader>
                            <CardContent>{children}</CardContent>
                        </Card>
                    </main>
                </div>
            </div>
        </SidebarProvider>
    );
}
