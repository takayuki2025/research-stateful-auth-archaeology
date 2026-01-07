import { toTypedSchema } from "@vee-validate/zod";
import * as z from "zod";

// --- Login Schema (ログイン用) ---
// LaravelのLoginRequestに基づいています
export const loginSchema = toTypedSchema(
  z.object({
    // required, email, string
    email: z
      .string()
      .min(1, { message: "メールアドレスを入力してください。" })
      .email({ message: "メールアドレスの形式が正しくありません。" }),

    // required, string
    password: z.string().min(1, { message: "パスワードを入力してください。" }),
  })
);

// Loginフォームの型定義
export type LoginFormValues = z.infer<typeof loginSchema>;

// --- Register Schema (新規登録用) ---
// LaravelのRegisterRequestに基づいています
export const registerSchema = toTypedSchema(
  z
    .object({
      // required, max:255
      name: z
        .string()
        .min(1, { message: "お名前を入力してください。" })
        .max(255, { message: "お名前は255文字以下で入力してください。" }),

      // required, email, max:255
      email: z
        .string()
        .min(1, { message: "メールアドレスを入力してください。" })
        .email({ message: "メールアドレスの形式が正しくありません。" })
        .max(255, {
          message: "メールアドレスは255文字以下で入力してください。",
        }),

      // required, min:8, confirmed
      password: z
        .string()
        .min(8, { message: "パスワードは８文字以上で入力してください。" }),

      // required
      password_confirmation: z
        .string()
        .min(1, { message: "確認用パスワードを入力してください。" }),
    })
    .refine((data) => data.password === data.password_confirmation, {
      // 'confirmed' ルールに対応
      message: "パスワードと一致しません。",
      path: ["password_confirmation"], // エラーを confirmation フィールドに付与
    })
);

// Registerフォームの型定義
export type RegisterFormValues = z.infer<typeof registerSchema>;
