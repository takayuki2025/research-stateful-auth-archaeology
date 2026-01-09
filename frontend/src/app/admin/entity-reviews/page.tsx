"use client";

import useSWR from "swr";
import ReviewCard from "./ReviewCard";

export default function ReviewQueuePage() {
  const { data } = useSWR("/api/entity-reviews");

  if (!data) return <p>Loading...</p>;

  return (
    <div className="space-y-4 p-6">
      {data.map((review: any) => (
        <ReviewCard key={review.id} review={review} />
      ))}
    </div>
  );
}
