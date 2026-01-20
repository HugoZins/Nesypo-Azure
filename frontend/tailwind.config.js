/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./src/**/*.{ts,tsx,js,jsx}",
        "./app/**/*.{ts,tsx,js,jsx}",
    ],
    theme: {
        extend: {
            colors: {
                border: "var(--border)",
                background: "var(--background)",
                foreground: "var(--foreground)",
                ring: "var(--ring)",
            },
            borderColor: {
                border: "var(--border)",
            },
            backgroundColor: {
                background: "var(--background)",
            },
            textColor: {
                foreground: "var(--foreground)",
            },
            ringColor: {
                ring: "var(--ring)",
            },
        },
    },
};
