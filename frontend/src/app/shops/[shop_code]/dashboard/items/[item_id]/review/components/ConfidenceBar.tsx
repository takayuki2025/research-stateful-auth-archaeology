type Props = {
  value: number; // 0.0 - 1.0
};

export function ConfidenceBar({ value }: Props) {
  const percent = Math.round(value * 100);

  const color =
    percent >= 85 ? "#22c55e" : percent >= 60 ? "#facc15" : "#ef4444";

  return (
    <div style={{ marginBottom: 12 }}>
      <div style={{ fontSize: 12, marginBottom: 4 }}>信頼度 {percent}%</div>
      <div style={{ background: "#e5e7eb", height: 8, borderRadius: 4 }}>
        <div
          style={{
            width: `${percent}%`,
            height: 8,
            background: color,
            borderRadius: 4,
            transition: "width 0.3s ease",
          }}
        />
      </div>
    </div>
  );
}
