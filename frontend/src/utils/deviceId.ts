export function getDeviceId(): string {
  let d = localStorage.getItem("device_id");
  if (!d) {
    d = crypto.randomUUID();
    localStorage.setItem("device_id", d);
  }
  return d;
}
