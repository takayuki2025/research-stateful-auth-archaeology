export async function fetchReviews() {
  return fetch("http://localhost:8000/v1/reviews").then((r) => r.json());
}

export async function resolveReview(id: number, action: string) {
  return fetch(`http://localhost:8000/v1/reviews/${id}/resolve`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ action }),
  });
}
