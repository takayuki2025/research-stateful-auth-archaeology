"use client";

import { initializeApp, getApps } from "firebase/app";
import { getAuth, type Auth } from "firebase/auth";

let firebaseAuth: Auth | null = null;

export function getFirebaseApp() {
  if (typeof window === "undefined") {
    throw new Error("Firebase must be used in client-side only");
  }

  const config = {
    apiKey: process.env.NEXT_PUBLIC_FIREBASE_API_KEY!,
    authDomain: process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN!,
    projectId: process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID!,
    storageBucket: process.env.NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET!,
    messagingSenderId: process.env.NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID!,
    appId: process.env.NEXT_PUBLIC_FIREBASE_APP_ID!,
  };

  if (getApps().length === 0) {
    return initializeApp(config);
  }

  return getApps()[0];
}

export function getFirebaseAuth(): Auth {
  if (!firebaseAuth) {
    firebaseAuth = getAuth(getFirebaseApp());
  }
  return firebaseAuth;
}
