"use client";

import type { Auth } from "firebase/auth";
import {
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  signOut,
  User,
} from "firebase/auth";
import { getFirebaseAuth } from "@/lib/firebase";

export class FirebaseAuthClient {
  private auth: Auth;

  constructor() {
    if (typeof window === "undefined") {
      throw new Error("FirebaseAuthClient must be used on client only");
    }
    this.auth = getFirebaseAuth();
  }

  async register(email: string, password: string): Promise<User> {
    const result = await createUserWithEmailAndPassword(
      this.auth,
      email,
      password,
    );
    return result.user;
  }

  async login(email: string, password: string): Promise<User> {
    const result = await signInWithEmailAndPassword(this.auth, email, password);
    return result.user;
  }

  /** ★ 常に最新トークンを取得（超重要） */
  async getFreshIdToken(user: User): Promise<string> {
    return user.getIdToken(true); // ← true 必須
  }

  async logout(): Promise<void> {
    await signOut(this.auth);
  }
}
