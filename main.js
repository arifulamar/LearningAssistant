// Firebase Authentication - EduConnect
// Menggunakan Firebase Compat SDK yang sudah tersedia di folder /firebase.
const firebaseConfig = {
  apiKey: "AIzaSyDFbAt043YWvZ7wwKvLnOFYYdaR8SwEgOo",
  authDomain: "educonnect-47dcb.firebaseapp.com",
  projectId: "educonnect-47dcb",
  storageBucket: "educonnect-47dcb.firebasestorage.app",
  messagingSenderId: "271361548411",
  appId: "1:271361548411:web:353562a08eabe0ba9082de",
  measurementId: "G-1R7N9T3TSJ"
};

// Hindari inisialisasi ganda ketika file ini dipanggil kembali.
if (!firebase.apps.length) {
  firebase.initializeApp(firebaseConfig);
}

const auth = firebase.auth();
const googleProvider = new firebase.auth.GoogleAuthProvider();
googleProvider.setCustomParameters({ prompt: "select_account" });

const LOGIN_PAGE = "index.html";
const DASHBOARD_PAGE = "dashboard.php";
const isLoginPage = () => /(?:^|\/)index\.html$/.test(window.location.pathname);

let sessionSyncInProgress = false;

// Simpan identitas Firebase ke session PHP agar dashboard PHP dapat melindungi halaman.
async function saveSession(user) {
  if (!user) throw new Error("Pengguna Firebase tidak ditemukan.");

  const response = await fetch("session.php", {
    method: "POST",
    credentials: "same-origin",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      name: user.displayName || user.email || "Pengguna",
      email: user.email,
      photoURL: user.photoURL || ""
    })
  });

  if (!response.ok) {
    throw new Error("Server gagal membuat session (HTTP " + response.status + ").");
  }

  const data = await response.json();
  if (!data.success) {
    throw new Error(data.message || "Gagal membuat session PHP.");
  }
  return data;
}

// Pulihkan hasil login redirect (misalnya popup diblokir browser).
auth.getRedirectResult().then(async (result) => {
  if (result && result.user) {
    await saveSession(result.user);
    window.location.replace(DASHBOARD_PAGE);
  }
}).catch((error) => {
  console.error("Login redirect gagal:", error);
  showAuthError(error);
});

// Periksa status login saat halaman dibuka/di-refresh.
auth.onAuthStateChanged(async (user) => {
  if (!user || sessionSyncInProgress) return;

  sessionSyncInProgress = true;
  try {
    // Pastikan session PHP terbentuk meskipun tab dimuat ulang.
    await saveSession(user);
    if (isLoginPage()) {
      window.location.replace(DASHBOARD_PAGE);
    }
  } catch (error) {
    console.error("Sinkronisasi session gagal:", error);
    showAuthError(error);
  } finally {
    sessionSyncInProgress = false;
  }
});

// Dipanggil langsung oleh tombol Login dengan Google.
async function loginWithGoogle() {
  const button = document.querySelector(".gsi-material-button");
  if (button) button.disabled = true;

  try {
    const result = await auth.signInWithPopup(googleProvider);
    await saveSession(result.user);
    window.location.replace(DASHBOARD_PAGE);
  } catch (error) {
    console.error("Login Google gagal:", error);
    if (error.code === "auth/popup-blocked" ||
        error.code === "auth/popup-closed-by-user" ||
        error.code === "auth/operation-not-supported-in-this-environment") {
      try {
        await auth.signInWithRedirect(googleProvider);
        return;
      } catch (redirectError) {
        console.error("Login redirect gagal:", redirectError);
        showAuthError(redirectError);
      }
    } else {
      showAuthError(error);
    }
  } finally {
    if (button) button.disabled = false;
  }
}

// Logout Firebase dan session PHP.
async function logout() {
  try {
    await auth.signOut();
    await fetch("logout.php", {
      method: "POST",
      credentials: "same-origin"
    });
  } catch (error) {
    console.error("Logout gagal:", error);
  } finally {
    window.location.replace(LOGIN_PAGE);
  }
}

function showAuthError(error) {
  const messages = {
    "auth/unauthorized-domain":
      "Domain situs belum diizinkan di Firebase Authentication. Tambahkan domain pada Authorized domains.",
    "auth/operation-not-allowed":
      "Provider Google belum diaktifkan di Firebase Authentication.",
    "auth/invalid-api-key":
      "API Key Firebase tidak valid. Periksa konfigurasi Firebase.",
    "auth/network-request-failed":
      "Koneksi internet bermasalah. Periksa jaringan lalu coba kembali."
  };
  const message = messages[error && error.code] ||
    (error && error.message) || "Terjadi kesalahan autentikasi.";
  const target = document.getElementById("auth-error");
  if (target) {
    target.textContent = message;
    target.style.display = "block";
  } else {
    alert("Login Firebase: " + message);
  }
}

window.loginWithGoogle = loginWithGoogle;
window.logout = logout;
