import type { NextRequest } from "next/server"
import { NextResponse } from "next/server"

export function middleware(req: NextRequest) {
	const token = req.cookies.get("token")?.value

	if (!token) {
		const url = req.nextUrl.clone()
		url.pathname = "/login"
		return NextResponse.redirect(url)
	}

	return NextResponse.next()
}

export const config = {
	matcher: ["/dashboard/:path*", "/profile/:path*", "/settings/:path*"],
}
