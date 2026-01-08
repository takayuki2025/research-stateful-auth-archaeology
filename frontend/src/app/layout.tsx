import "@/app/globals.css";
import HeaderMain from "@/components/layout/HeaderMain";
import { AppProviders } from "./providers";

export default function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <html lang="ja">
      <body>
        <AppProviders>
          <HeaderMain />
          <main className="pt-[70px]">{children}</main>
        </AppProviders>
      </body>
    </html>
  );
}
