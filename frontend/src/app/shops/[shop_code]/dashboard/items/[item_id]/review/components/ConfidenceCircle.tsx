type Props = {
  value: number; // 0.0 - 1.0
};

export function ConfidenceCircle({ value }: Props) {
  const percent = Math.round(value * 100);
  const stroke =
    percent >= 85 ? "#22c55e" : percent >= 60 ? "#facc15" : "#ef4444";

  const radius = 25;
  const circumference = 2 * Math.PI * radius;
  const dash = (percent / 100) * circumference;

  return (
    <svg width="70" height="70">
      <circle
        cx="35"
        cy="35"
        r={radius}
        stroke="#e5e7eb"
        strokeWidth="6"
        fill="none"
      />
      <circle
        cx="35"
        cy="35"
        r={radius}
        stroke={stroke}
        strokeWidth="6"
        fill="none"
        strokeDasharray={`${dash} ${circumference}`}
        transform="rotate(-90 35 35)"
      />
      <text x="35" y="40" textAnchor="middle" fontSize="14" fontWeight="bold">
        {percent}%
      </text>
    </svg>
  );
}
