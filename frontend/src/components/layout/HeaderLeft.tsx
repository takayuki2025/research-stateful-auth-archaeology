"use client";

import Link from "next/link";
import styles from "./HeaderLeft.module.css";

export default function HeaderLeft({ href = "/" }: any) {
  return (
    <div className={styles.root}>
      <Link href={href} className={styles.link}>
        <div className={styles.logoWrap}>
          <svg className={styles.logo} viewBox="0 0 100 100">
            <defs>
              <linearGradient
                id="silver-metallic"
                x1="0%"
                y1="0%"
                x2="100%"
                y2="100%"
              >
                <stop offset="0%" stopColor="#FFFFFF" />
                <stop offset="25%" stopColor="#D1D1D1" />
                <stop offset="50%" stopColor="#7A7A7A" />
                <stop offset="75%" stopColor="#C2C2C2" />
                <stop offset="100%" stopColor="#FFFFFF" />
              </linearGradient>
            </defs>

            {/* 4つのリング：上2つ・下2つの配置 */}
            <g stroke="url(#silver-metallic)" strokeWidth="4" fill="none">
              {/* 左上 */}
              <circle cx="40" cy="40" r="20" opacity="0.8" />
              {/* 右上 */}
              <circle cx="60" cy="40" r="20" opacity="0.8" />
              {/* 左下 */}
              <circle cx="40" cy="60" r="20" opacity="0.8" />
              {/* 右下 */}
              <circle cx="60" cy="60" r="20" opacity="0.8" />
            </g>

            {/* 中央の「O」 (Core) */}
            <circle
              cx="50"
              cy="50"
              r="15"
              stroke="url(#silver-metallic)"
              strokeWidth="6"
              fill="black"
            />

            {/* Oの内側の装飾線 */}
            <circle
              cx="50"
              cy="50"
              r="10"
              stroke="url(#silver-metallic)"
              strokeWidth="1"
              fill="none"
              opacity="0.4"
            />
          </svg>
        </div>
      </Link>
    </div>
  );
}
