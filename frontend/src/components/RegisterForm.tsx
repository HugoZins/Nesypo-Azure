"use client"

import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { registerSchema } from "@/lib/validation/auth"
import { z } from "zod"

type RegisterFormValues = z.infer<typeof registerSchema>

export default function RegisterForm() {
    const {
        register,
        handleSubmit,
        formState: { errors },
    } = useForm<RegisterFormValues>({
        resolver: zodResolver(registerSchema),
    })

    const onSubmit = (data: RegisterFormValues) => {
        console.log(data)
    }

    return (
        <form onSubmit={handleSubmit(onSubmit)}>
            <input {...register("email")} placeholder="Email" />
            <p>{errors.email?.message}</p>

            <input {...register("password")} type="password" placeholder="Password" />
            <p>{errors.password?.message}</p>

            <input
                {...register("passwordConfirm")}
                type="password"
                placeholder="Confirm password"
            />
            <p>{errors.passwordConfirm?.message}</p>

            <button type="submit">Register</button>
        </form>
    )
}
