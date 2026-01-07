import axios from "axios";

export const publicClient = axios.create({

    baseURL: process.env.NEXT_PUBLIC_API_BASE_URL || "/api",
    withCredentials: true,
});
