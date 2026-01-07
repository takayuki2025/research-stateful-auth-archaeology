import "./bootstrap";
import React from "react";
import { createRoot } from "react-dom/client";

// ----------------------------------------------------
// メインのReactコンポーネント
// ----------------------------------------------------
function App() {
    return (
        <div
            style={{
                minHeight: "100vh",
                display: "flex",
                flexDirection: "column",
                alignItems: "center",
                justifyContent: "center",
                backgroundColor: "#1E293B",
                color: "#94A3B8",
                fontFamily: "sans-serif",
            }}
        >
            <h1 style={{ fontSize: "3rem", color: "#10B981" }}>
                🎉 Setup Successful!
            </h1>
            <p style={{ fontSize: "1.5rem", marginTop: "1rem" }}>
                Laravel (Nginx/PHP) is talking to React (Vite)!
            </p>
            <p style={{ marginTop: "2rem" }}>
                This content is rendered by the React App in **app.jsx**.
            </p>
        </div>
    );
}

// ----------------------------------------------------
// DOMへのマウント
// ----------------------------------------------------
const container = document.getElementById("app");
if (container) {
    const root = createRoot(container);
    root.render(<App />);
}
