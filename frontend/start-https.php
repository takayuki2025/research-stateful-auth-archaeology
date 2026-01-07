import { createServer } from "https";
import next from "next";
import fs from "fs";
import url from "url";

const port = 3000;
const dev = process.env.NODE_ENV !== "production";

const app = next({
  dev,
  hostname: "localhost",
  port,
});

const handle = app.getRequestHandler();

// mkcert で生成した証明書を使用（あなたの環境に合わせる）
const httpsOptions = {
  key: fs.readFileSync("./ssl_certs/localhost+2-key.pem"),
  cert: fs.readFileSync("./ssl_certs/localhost+2.pem"),
};

app.prepare().then(() => {
  createServer(httpsOptions, (req, res) => {
    const parsedUrl = url.parse(req.url, true);
    handle(req, res, parsedUrl);
  }).listen(port, () => {
    console.log(`> Ready on https://localhost:${port}`);
  });
});
