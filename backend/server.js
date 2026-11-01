const express = require("express");
const cors = require("cors");
const helmet = require("helmet");
const { rateLimit } = require("express-rate-limit");
const { initializeApp, applicationDefault } = require("firebase-admin/app");
const { getAuth } = require("firebase-admin/auth");

const app = express();
const PORT = Number(process.env.PORT || 8080);
const PROJECT_ID = process.env.FIREBASE_PROJECT_ID || "educonnect-47dcb";
const GEMINI_MODEL = process.env.GEMINI_MODEL || "gemini-2.5-flash";
const GEMINI_API_KEY = process.env.GEMINI_API_KEY;
const allowedOrigins = (process.env.ALLOWED_ORIGINS || "https://arifulamar.github.io")
  .split(",").map((value) => value.trim()).filter(Boolean);

if (!GEMINI_API_KEY) {
  console.error("Missing GEMINI_API_KEY secret.");
  process.exit(1);
}

// Cloud Run supplies Application Default Credentials through its attached
// runtime service account. Do not upload or commit a service-account JSON key.
initializeApp({ credential: applicationDefault(), projectId: PROJECT_ID });

app.disable("x-powered-by");
app.use(helmet());
app.use(cors({
  origin(origin, callback) {
    // No Origin is allowed for health checks and server-to-server requests;
    // protected API requests still require a valid Firebase ID token.
    if (!origin || allowedOrigins.includes(origin)) return callback(null, true);
    return callback(new Error("Origin not allowed"));
  },
  methods: ["GET", "POST", "OPTIONS"],
  allowedHeaders: ["Content-Type", "Authorization"],
  maxAge: 600
}));
app.use(express.json({ limit: "20kb", strict: true }));

app.get("/health", (_req, res) => res.status(200).json({ status: "ok" }));

async function requireFirebaseUser(req, res, next) {
  const authorization = req.get("authorization") || "";
  const match = authorization.match(/^Bearer (.+)$/i);
  if (!match) return res.status(401).json({ reply: "Silakan login dengan akun Google terlebih dahulu." });

  try {
    // checkRevoked=true also rejects revoked/disabled user sessions.
    req.firebaseUser = await getAuth().verifyIdToken(match[1], true);
    return next();
  } catch (_error) {
    return res.status(401).json({ reply: "Sesi login tidak valid atau sudah berakhir. Silakan login kembali." });
  }
}

// Per-instance safeguard. Also cap Cloud Run max instances and Gemini quotas;
// for strict global quotas, use a shared store such as Memorystore/Firestore.
const userRateLimit = rateLimit({
  windowMs: 60 * 1000,
  limit: 15,
  standardHeaders: "draft-8",
  legacyHeaders: false,
  keyGenerator: (req) => req.firebaseUser.uid,
  message: { reply: "Batas 15 pertanyaan per menit tercapai. Coba lagi sebentar." }
});

app.post("/api/chat", requireFirebaseUser, userRateLimit, async (req, res) => {
  const message = typeof req.body?.message === "string" ? req.body.message.trim() : "";
  if (!message) return res.status(400).json({ reply: "Silakan ketik pertanyaan Anda." });
  if (message.length > 8000) return res.status(413).json({ reply: "Pesan terlalu panjang. Maksimal 8.000 karakter." });

  // Retain the existing lightweight table example without spending Gemini quota.
  if (/^tabel\\s*/i.test(message)) {
    const html = "<table border='1' cellpadding='5' cellspacing='0'><tr><th>Nama</th><th>Nilai</th></tr><tr><td>Ani</td><td>90</td></tr><tr><td>Budi</td><td>80</td></tr></table>";
    return res.json({ type: "table", html });
  }

  const chartMatch = message.match(/^grafik\\s*(.*)$/i);
  if (chartMatch) {
    const labels = [];
    const values = [];
    for (const pair of chartMatch[1].trim().split(/\\s+/)) {
      if (!pair.includes(":")) continue;
      const [name, rawValue] = pair.split(":", 2);
      const value = Number.parseInt(rawValue.trim(), 10);
      if (name.trim() && Number.isFinite(value)) {
        labels.push(name.trim().charAt(0).toUpperCase() + name.trim().slice(1));
        values.push(value);
      }
    }
    if (labels.length) return res.json({ type: "chart", config: {
      type: "bar",
      data: { labels, datasets: [{ label: "Nilai", data: values }] },
      options: { responsive: true, plugins: { title: { display: true, text: "Grafik Nilai" } } }
    }});
  }

  try {
    const upstream = await fetch(
      "https://generativelanguage.googleapis.com/v1beta/models/" + encodeURIComponent(GEMINI_MODEL) + ":generateContent",
      {
        method: "POST",
        headers: { "Content-Type": "application/json", "x-goog-api-key": GEMINI_API_KEY },
        body: JSON.stringify({ contents: [{ role: "user", parts: [{ text: message }] }] }),
        signal: AbortSignal.timeout(55000)
      }
    );

    const result = await upstream.json().catch(() => ({}));
    if (!upstream.ok) {
      console.error("Gemini upstream error:", upstream.status, result?.error?.status || "unknown");
      return res.status(502).json({ reply: "Layanan Gemini sedang bermasalah. Silakan coba kembali." });
    }

    const reply = result?.candidates?.[0]?.content?.parts?.map((part) => part.text || "").join("\\n").trim();
    if (!reply) return res.status(502).json({ reply: "Gemini tidak memberikan jawaban." });

    const imageMatch = reply.match(/!\\[[^\\]]*\\]\\((https?:\\/\\/[^\\s)]+)\\)/);
    if (imageMatch) return res.json({ type: "image", url: imageMatch[1], caption: "Gambar dari Gemini." });
    return res.json({ type: "text", reply });
  } catch (error) {
    console.error("Gemini request failed:", error.name || "Error");
    return res.status(502).json({ reply: "Tidak dapat menghubungi Gemini saat ini. Silakan coba kembali." });
  }
});

app.use((error, _req, res, _next) => {
  if (error.message === "Origin not allowed") return res.status(403).json({ reply: "Origin tidak diizinkan." });
  if (error instanceof SyntaxError) return res.status(400).json({ reply: "Format permintaan tidak valid." });
  console.error("Request error:", error.name || "Error");
  return res.status(500).json({ reply: "Terjadi kesalahan pada layanan." });
});

app.listen(PORT, "0.0.0.0", () => console.log("LearningAssistant API listening on", PORT));
