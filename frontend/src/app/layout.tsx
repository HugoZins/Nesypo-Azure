import "./globals.css";
import { QueryProvider } from "@/providers/QueryProvider";

export default function RootLayout({ children }: { children: React.ReactNode }) {
    return (
        <html lang="fr">
        <body className="min-h-screen bg-background text-foreground antialiased">
        <QueryProvider>{children}</QueryProvider>
        </body>
        </html>
    );
}
