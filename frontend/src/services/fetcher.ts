export const fetcher = async (url: string) => {
  console.log("[🔥FETCHER] url =", url);
  const res = await fetch(url, {
    credentials: "include",
  });

  if (!res.ok) {
    throw new Error("API Error");
  }

  return res.json();
};
