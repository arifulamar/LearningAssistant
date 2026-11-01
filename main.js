// Konfigurasi Firebase
const firebaseConfig = {
  apiKey: "AIzaSyDFbAt043YWvZ7wwKvLnOFYYdaR8SwEgOo",
  authDomain: "educonnect-47dcb.firebaseapp.com",
  projectId: "educonnect-47dcb",
  storageBucket: "educonnect-47dcb.firebasestorage.app",
  messagingSenderId: "271361548411",
  appId: "1:271361548411:web:353562a08eabe0ba9082de",
  measurementId: "G-1R7N9T3TSJ"
};

// Inisialisasi Firebase
firebase.initializeApp(firebaseConfig);
const auth = firebase.auth();
const provider = new firebase.auth.GoogleAuthProvider();

// Cek status login setiap halaman dimuat
auth.onAuthStateChanged(user => {
  if (user) {
    // Kalau user sudah login, refresh session
    saveSession(user, false);

    // Kalau di halaman login, langsung ke dashboard
    if (window.location.pathname.includes("index.html")) {
      window.location.href = "dashboard.php";
    }
  } else {
    // Kalau belum login dan bukan di halaman login, kembali ke login
    if (!window.location.pathname.includes("index.html")) {
      window.location.href = "index.html";
    }
  }
});

// Simpan session ke server
function saveSession(user, redirectToDashboard) {
  fetch("session.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      name: user.displayName,
      email: user.email,
      photoURL: user.photoURL
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      console.log("Session dibuat/diupdate");
      if (redirectToDashboard) {
        window.location.href = "dashboard.php";
      }
    } else {
      console.error("Gagal membuat session.");
    }
  })
  .catch(err => {
    console.error("Error kirim session:", err);
  });
}

// Fungsi login manual (dipanggil hanya dari klik user)
function loginWithGoogle() {
  auth.signInWithPopup(provider)
    .then(result => {
      saveSession(result.user, true);
    })
    .catch(error => {
      if (error.code === "auth/popup-blocked") {
        console.warn("Popup diblokir, coba pakai redirect.");
        auth.signInWithRedirect(provider);
      } else {
        console.error("Login gagal:", error.message);
      }
    });
}

// Fungsi logout
function logout() {
  auth.signOut().then(() => {
    fetch("logout.php").then(() => {
      window.location.href = "index.html";
    });
  });
}
