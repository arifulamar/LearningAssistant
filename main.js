// EduConnect - Firebase Authentication untuk GitHub Pages (static hosting)
const firebaseConfig = {
  apiKey: "AIzaSyDFbAt043YWvZ7wwKvLnOFYYdaR8SwEgOo",
  authDomain: "educonnect-47dcb.firebaseapp.com",
  projectId: "educonnect-47dcb",
  storageBucket: "educonnect-47dcb.firebasestorage.app",
  messagingSenderId: "271361548411",
  appId: "1:271361548411:web:353562a08eabe0ba9082de",
  measurementId: "G-1R7N9T3TSJ"
};

if (!firebase.apps.length) firebase.initializeApp(firebaseConfig);

const auth = firebase.auth();
const googleProvider = new firebase.auth.GoogleAuthProvider();
googleProvider.setCustomParameters({ prompt: "select_account" });

const LOGIN_PAGE = "index.html";
const DASHBOARD_PAGE = "dashboard.html";
const currentPage = window.location.pathname.split("/").pop();
const isDashboard = currentPage === DASHBOARD_PAGE;
const isLoginPage = !isDashboard;

function showAuthError(error) {
  const messages = {
    "auth/unauthorized-domain": "Domain situs belum diizinkan. Tambahkan domain GitHub Pages di Firebase Authentication > Settings > Authorized domains.",
    "auth/operation-not-allowed": "Login Google belum diaktifkan di Firebase Authentication.",
    "auth/invalid-api-key": "API Key Firebase tidak valid. Periksa konfigurasi Firebase.",
    "auth/network-request-failed": "Koneksi internet bermasalah. Silakan coba kembali.",
    "auth/popup-blocked": "Popup login diblokir browser. Izinkan popup atau coba kembali."
  };
  const message = messages[error?.code] || error?.message || "Terjadi kesalahan saat login.";
  const target = document.getElementById("auth-error");
  if (target) {
    target.textContent = message;
    target.style.display = "block";
  } else {
    alert(message);
  }
}

// Pulihkan sesi dari alur signInWithRedirect.
auth.getRedirectResult().catch(showAuthError);

// Firebase Auth adalah sumber status login; tidak membutuhkan PHP session.
auth.onAuthStateChanged((user) => {
  if (user) {
    if (isLoginPage) {
      window.location.replace(DASHBOARD_PAGE);
      return;
    }
    const photo = document.getElementById("user-photo");
    if (photo && user.photoURL) photo.src = user.photoURL;
    const name = document.getElementById("user-name");
    if (name) name.textContent = user.displayName || user.email || "Pengguna";
    const email = document.getElementById("user-email");
    if (email) email.textContent = user.email || "";
  } else if (isDashboard) {
    window.location.replace(LOGIN_PAGE);
  }
});

async function loginWithGoogle() {
  const button = document.querySelector(".gsi-material-button");
  if (button) button.disabled = true;

  try {
    await auth.signInWithPopup(googleProvider);
    // onAuthStateChanged akan mengarahkan ke dashboard.html.
  } catch (error) {
    console.error("Login Google gagal:", error);
    if (["auth/popup-blocked", "auth/popup-closed-by-user"].includes(error.code)) {
      try {
        await auth.signInWithRedirect(googleProvider);
        return;
      } catch (redirectError) {
        showAuthError(redirectError);
      }
    } else {
      showAuthError(error);
    }
  } finally {
    if (button) button.disabled = false;
  }
}

async function logout() {
  try {
    await auth.signOut();
    window.location.replace(LOGIN_PAGE);
  } catch (error) {
    console.error("Logout gagal:", error);
    showAuthError(error);
  }
}

window.loginWithGoogle = loginWithGoogle;
window.logout = logout;
