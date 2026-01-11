type Props = {
  value: number | null;
};

export function ConfidenceIndicator({ value }: Props) {
  if (value === null) return <span>—</span>;

  const percent = Math.round(value * 100);

  const color =
    percent >= 85 ? "#22c55e" : percent >= 65 ? "#facc15" : "#ef4444";

  return (
    <div style={{ display: "flex", gap: 12, alignItems: "center" }}>
      {/* バー */}
      <div style={{ width: 120, background: "#eee", height: 8 }}>
        <div style={{ width: `${percent}%`, height: 8, background: color }} />
      </div>

      {/* 円 */}
      <svg width="40" height="40">
        <circle
          cx="20"
          cy="20"
          r="18"
          stroke="#eee"
          strokeWidth="4"
          fill="none"
        />
        <circle
          cx="20"
          cy="20"
          r="18"
          stroke={color}
          strokeWidth="4"
          fill="none"
          strokeDasharray={`${percent} ${100 - percent}`}
          transform="rotate(-90 20 20)"
        />
      </svg>

      <strong>{percent}%</strong>
    </div>
  );
}
