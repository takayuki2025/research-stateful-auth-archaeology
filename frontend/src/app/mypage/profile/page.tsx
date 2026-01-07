"use client";

import React, {
  useState,
  useEffect,
  useCallback,
  useRef,
  useMemo,
} from "react";
import { useRouter, useSearchParams } from "next/navigation";

import { useAuth } from "@/ui/auth/useAuth";
import { getImageUrl, IMAGE_TYPE } from "@/utils/utils";
import styles from "./W-ProfilePage.module.css";

interface ProfileUser {
  id: number;
  display_name: string | null;
  email: string;
  post_number: string | null;
  address: string | null;
  building: string | null;
  user_image: string | null;
}

interface ProfileForm {
  display_name: string;
  post_number: string;
  address: string;
  building: string;
}

type ProfileErrors = {
  [K in keyof ProfileForm]?: string[];
} & {
  user_image?: string[];
};

export default function ProfilePage() {
  const router = useRouter();
  const searchParams = useSearchParams();

  const {
    user: authUser,
    authClient,
    isAuthenticated,
    isLoading: isAuthLoading,
    logout,
  } = useAuth();

  const isVerificationRedirect = useMemo(
    () => searchParams.get("verified") === "true",
    [searchParams]
  );

  const [profileUser, setProfileUser] = useState<ProfileUser | null>(null);
  const [form, setForm] = useState<ProfileForm>({
    display_name: "",
    post_number: "",
    address: "",
    building: "",
  });

  const [profileErrors, setProfileErrors] = useState<ProfileErrors>({});
  const [imageError, setImageError] = useState("");
  const [successMessage, setSuccessMessage] = useState("");

  const [isLoading, setIsLoading] = useState(true);
  const [isFetching, setIsFetching] = useState(false);
  const [isRecovering, setIsRecovering] = useState(false);

  const verificationHandledRef = useRef(false);
  const fileInputRef = useRef<HTMLInputElement | null>(null);

  const profileImageUrl = useMemo(() => {
    return getImageUrl(
      profileUser?.user_image ?? null,
      IMAGE_TYPE.USER,
      Date.now()
    );
  }, [profileUser?.user_image]);

  const initializeProfileFromResponse = useCallback((src: any) => {
    const data: ProfileUser = src?.user ?? src;

    setProfileUser(data);
    setForm({
      display_name: data.display_name ?? "",
      post_number: data.post_number ?? "",
      address: data.address ?? "",
      building: data.building ?? "",
    });
  }, []);

  const fetchUserProfile = useCallback(
    async (isRetry = false) => {
      if (!isRetry) {
        setIsFetching(true);
        setSuccessMessage("");
        setProfileErrors({});
      }

      try {
        const data = await authClient.get("/mypage/profile");
        initializeProfileFromResponse(data);
        setIsLoading(false);
        setIsRecovering(false);
      } catch (err: any) {
        if (err?.message === "Unauthenticated") {
          await logout();
          router.replace("/login");
          return;
        }
        setIsLoading(false);
      } finally {
        if (!isRetry) setIsFetching(false);
      }
    },
    [authClient, initializeProfileFromResponse, logout, router]
  );

  // メール認証完了後の再同期
  useEffect(() => {
    if (!isVerificationRedirect) return;
    if (verificationHandledRef.current) return;

    verificationHandledRef.current = true;

    const run = async () => {
      try {
        setIsRecovering(true);
        const me = await authClient.me();
        if (!me) {
          await logout();
          router.replace("/login");
        }
      } finally {
        setIsRecovering(false);
      }
    };
    run();
  }, [isVerificationRedirect, authClient, logout, router]);

  // 初期ロード
  useEffect(() => {
    if (isAuthLoading || isRecovering) return;

    if (!isAuthenticated) {
      router.replace("/login");
      return;
    }

    if (!profileUser && !isFetching) {
      fetchUserProfile();
    }
  }, [
    isAuthLoading,
    isRecovering,
    isAuthenticated,
    profileUser,
    isFetching,
    fetchUserProfile,
    router,
  ]);

  // プロフィール画像アップロード
  const handleImageUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setImageError("");
    setIsLoading(true);

    const formData = new FormData();
    formData.append("user_image", file);

    try {
      const res = await fetch("/mypage/profile/image", {
        method: "POST",
        credentials: "include",
        body: formData,
      });

      if (!res.ok) throw await res.json();

      const data = await res.json();
      initializeProfileFromResponse(data);
      setSuccessMessage("画像を更新しました！");
    } catch (err: any) {
      setImageError(
        err?.errors?.user_image?.[0] ?? "画像アップロードに失敗しました。"
      );
    } finally {
      setIsLoading(false);
      if (fileInputRef.current) fileInputRef.current.value = "";
    }
  };

  // プロフィール更新
  const handleProfileUpdate = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();

    setProfileErrors({});
    setIsLoading(true);

    try {
      const res = await authClient.post("/mypage/profile", form);
      initializeProfileFromResponse(res);
      setSuccessMessage(
        profileUser
          ? "プロフィールを更新しました！"
          : "プロフィールを作成しました！"
      );
    } catch (err: any) {
      if (err?.errors) {
        setProfileErrors(err.errors);
      } else {
        setSuccessMessage("更新時にエラーが発生しました。");
      }
    } finally {
      setIsLoading(false);
    }
  };

  /* =========================
     Render
  ========================= */

  if (isAuthLoading || isLoading || isRecovering) {
    return (
      <div className={`${styles.login_page} max-w-[1400px] mx-auto pt-5 pb-10`}>
        <h2 className={styles.title}>プロフィール設定</h2>
        <div className="text-center p-8">
          <div className="animate-spin rounded-full h-10 w-10 border-t-2 border-b-2 border-red-500 mx-auto"></div>
          <p className="text-gray-500 mt-3">
            {isRecovering ? "セッションを再同期しています..." : "読み込み中..."}
          </p>
        </div>
      </div>
    );
  }

  if (!isAuthenticated || !profileUser) {
    return (
      <div className={`${styles.login_page} max-w-[1400px] mx-auto pt-5 pb-10`}>
        <h2 className={styles.title}>プロフィール設定</h2>
        <p>認証エラーが発生しました。ログインし直してください。</p>
      </div>
    );
  }

  return (
    <div
      className={`${styles.login_page} max-w-[1400px] mx-auto pt-5 pb-10`}
      key={authUser?.id || "unauthenticated"}
    >
      <h2 className={styles.title}>プロフィール設定</h2>

      <div className={styles["form-wrapper"]}>
        {successMessage && (
          <div className={styles["alert-success2"]}>{successMessage}</div>
        )}

        {/* 画像アップロード */}
        <form
          onSubmit={(e) => e.preventDefault()}
          className={styles.item_sell_contents_box_line}
        >
          <div className={styles.image_name}>
            <div className={styles.image_button_row}>
              <img
                key={profileUser.user_image || "default"}
                src={profileImageUrl}
                alt="プロフィール画像"
                className={styles.user_image_css}
              />
              <button
                type="button"
                className={styles.upload_submit}
                onClick={() => fileInputRef.current?.click()}
                disabled={isLoading}
              >
                画像を選択する
              </button>
            </div>

            <input
              type="file"
              name="user_image"
              ref={fileInputRef}
              style={{ display: "none" }}
              onChange={handleImageUpload}
              accept="image/*"
            />
          </div>

          <div className={styles.user_image_error_message}>{imageError}</div>
        </form>

        {/* プロフィール更新フォーム */}
        <form onSubmit={handleProfileUpdate}>
          {/* ユーザー名 */}
          <div className={styles["form-group"]}>
            <label htmlFor="name" className={styles.label_form_1}>
              ユーザー名
            </label>
            <input
              id="display_name"
              name="display_name"
              type="text"
              className={styles.name_form}
              value={form.display_name}
              onChange={(e) =>
                setForm((prev) => ({ ...prev, display_name: e.target.value }))
              }
            />
            <div className={styles.profile__error}>
              {profileErrors.name ? profileErrors.name[0] : ""}
            </div>
          </div>

          {/* 郵便番号 */}
          <div className={styles["form-group"]}>
            <label htmlFor="post_number" className={styles.label_form_2}>
              郵便番号 (8桁、ハイフンあり)
            </label>
            <input
              id="post_number"
              type="text"
              className={styles.email_form}
              name="post_number"
              value={form.post_number}
              onChange={(e) =>
                setForm((prev) => ({ ...prev, post_number: e.target.value }))
              }
              placeholder="例: 100-0001"
              maxLength={8}
            />
            <div className={styles.profile__error}>
              {profileErrors.post_number ? profileErrors.post_number[0] : ""}
            </div>
          </div>

          {/* 住所 */}
          <div className={styles["form-group"]}>
            <label htmlFor="address" className={styles.label_form_3}>
              住所
            </label>
            <input
              id="address"
              type="text"
              className={styles.password_form}
              name="address"
              value={form.address}
              onChange={(e) =>
                setForm((prev) => ({ ...prev, address: e.target.value }))
              }
              placeholder="手動で入力してください"
            />
            <div className={styles.profile__error}>
              {profileErrors.address ? profileErrors.address[0] : ""}
            </div>
          </div>

          {/* 建物名 */}
          <div className={styles["form-group"]}>
            <label htmlFor="building" className={styles.label_form_4}>
              建物名
            </label>
            <input
              id="building"
              type="text"
              className={styles.password_form}
              name="building"
              value={form.building}
              onChange={(e) =>
                setForm((prev) => ({ ...prev, building: e.target.value }))
              }
            />
            <div className={styles.profile__error}>
              {profileErrors.building ? profileErrors.building[0] : ""}
            </div>
          </div>

          <div className={styles.submit}>
            <input
              type="submit"
              className={styles.submit_form}
              value="更新する"
              disabled={isLoading}
            />
          </div>
        </form>
      </div>
    </div>
  );
}
