
<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.html"); // atau index.php
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduConnect ~ Portal Materi Ajar SMA Negeri 1 Sabang Berbasis AI</title>
    
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    
    <!-- Firebase SDK -->
   <script src="firebase/firebase-app-compat.js"></script>
<script src="firebase/firebase-auth-compat.js"></script>

<style>
/* Reset */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

html, body {
    background-color: #f4f6f8;
    margin: 0;
    padding: 0;
    height: 100%;
}



.container {
    min-height: 100vh; /* Tinggi penuh viewport */
    width: 100%;
    background-color: #f5f5f5; /* Bisa diganti warna background */
    display: flex;
    flex-direction: column;
}

/* Header */

.header {
    background-color: #4169e1;
    color: white;
    padding: 30px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: relative; /* supaya tetap di alur dokumen */
}
.dashboard-header {
    background-color: #4169e1;
    color: white;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 20px;
    position: fixed;
    width: 100%;
    top: 0;
    left: 0;
    right:0;
    z-index: 1000;
}

/* Foto Profil */
.profile-pic {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    cursor: pointer;
}

/* Dropdown */
.profile-menu {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    right: 0;
    top: 40px;
    background: white;
    color: black;
    border-radius: 6px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    display: none;
}
.dropdown {
    position: relative;
    display: inline-block;
}

.dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-radius: 5px;
    min-width: 120px;
    z-index: 100;
}
.dropdown-menu a {
    display: block;
    padding: 10px 15px;
    text-decoration: none;
    color: black;
}

.dropdown-menu a:hover {
    background-color: #f0f0f0;
}


  /* Tombol hamburger */
    .hamburger {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #f2e9e9;
      margin: 10px;
      z-index: 1100; /* biar di atas overlay */
      position: relative;
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      top: 0;
      left: -250px; /* sembunyi */
      width: 250px;
      height: 100%;
      background: #4169e1;
      color: #fff;
      padding: 20px;
      transition: left 0.3s ease;
      z-index: 1200; /* di atas overlay */
      overflow-y: auto;  
    }
    .sidebar.active {
      left: 0;
    }

    /* Overlay */
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      display: none;
      z-index: 1000;
    }
    .overlay.active {
      display: block;
    }

    /* Link menu */
    .sidebar ul {
      list-style: none;
      padding: 0;
    }
    .sidebar ul li {
      margin: 15px 0;
    }
    .sidebar ul li a {
      color: white;
      text-decoration: none;
    }

/* tombol dropdown */
.dropdown-btn {
  background: none;
  border: none;
  color: white;
  padding: 10px 0;
  width: 100%;
  text-align: left;
  cursor: pointer;
  font-size: 16px;
}

/* container awalnya hidden */
.dropdown-container {
  display: none;
  list-style: none;
  padding-left: 15px;
}

.dropdown-container li a {
  display: block;
  padding: 5px 0;
  color: #ddd;
  text-decoration: none;
}

.dropdown-container li a:hover {
  color: #fff;
}
pre {
  margin: 3px 0;
  padding: 5px;
  background: #eee;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
  color: #000;  
  font-size: 10px;   /* ðŸ‘‰ kecilin font */
  line-height: 1.4;  /* ðŸ‘‰ biar lebih rapat */
}

  pre:hover {
    background: #ddd;
  }
  
/* === Chatbox === */
#chatbox {
  background: white;
  width: 100%;             /* full lebar */
  height: calc(100vh - 80px); /* penuh layar minus header */
  display: flex;
  flex-direction: column;
  border-radius: 0;        /* biar rata ke layar */
  box-shadow: none;        /* hilangkan shadow di desktop */
  overflow: hidden;
}

#chatbox h2 {
  margin: 0;
  padding: 15px;
  background: #2563eb;
  color: white;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

#messages {
  flex: 1;
  overflow-y: auto;
  padding: 15px;
  background: #f9fafb;
}

/* Bubble pesan */
.msg-row {
  display: flex;
  margin-bottom: 12px;
}
.msg-row.user { justify-content: flex-end; }
.msg-row.bot { justify-content: flex-start; }

.msg {
  max-width: 70%;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 14px;
  line-height: 1.4;
}
.msg-row.user .msg {
  background: #2563eb;
  color: white;
  border-bottom-right-radius: 2px;
}
.msg-row.bot .msg {
  background: #e5e7eb;
  color: #111;
  border-bottom-left-radius: 2px;
}

/* Input area */
#inputArea {
  display: flex;
  border-top: 1px solid #ddd;
  padding: 10px;
  gap: 10px;
}
#userInput {
  flex: 1;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 14px;
}
#inputArea button {
  background: #2563eb;
  color: white;
  border: none;
  border-radius: 8px;
  padding: 0 16px;
  cursor: pointer;
}
#inputArea button:hover {
  background: #1d4ed8;
}

/* === Responsif Mobile === */
@media (max-width: 600px) {
  #chatbox {
    height: calc(100vh - 60px); /* lebih ringkas di hp */
  }
}
.avatar-img {
  width: 20px;   /* bisa diganti sesuai kebutuhan */
  height: 20px;
  border-radius: 50%; /* biar bulat */
  object-fit: cover;
}
table {
      border-collapse: collapse;
      width: 100%;
      margin-top: 6px;
    }
    table, th, td {
      border: 1px solid #aaa;
      padding: 6px;
      text-align: center;
    }
</style>

</head>
<body>
   <div class="header">
       <header class="dashboard-header">
        <button class="hamburger" onclick="toggleSidebar()">
          <i class="fa-solid fa-burger"></i>
        </button>



<div id="sidebar" class="sidebar">
  <h2>Menu Pelajaran</h2>
  <ul>
    

    <!-- ================= FASE E ================= -->
    <li>
      <button class="dropdown-btn">Fase E (Kelas X)</button>
      <ul class="dropdown-container">

        <!-- Bahasa Indonesia -->
        <li><strong>Bahasa Indonesia</strong>
          <div class="copy-box"><pre>Menganalisis teks anekdot <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Menyusun teks eksposisi <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Menganalisis teks cerita pendek <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Menulis teks cerpen <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Menganalisis teks negosiasi <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Menulis teks negosiasi <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
        </li>

        <!-- Bahasa Inggris -->
        <li><strong>Bahasa Inggris</strong>
          <div class="copy-box"><pre>Introducing self and others <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Descriptive texts <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Narrative texts <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Exposition texts <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Procedure texts <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
        </li>

        <!-- Matematika -->
        <li><strong>Matematika</strong>
          <div class="copy-box"><pre>Persamaan linear dan kuadrat <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Fungsi dan grafik <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Trigonometri dasar <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Statistika deskriptif <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Peluang sederhana <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
        </li>

        <!-- Fisika -->
        <li><strong>Fisika</strong>
          <div class="copy-box"><pre>Besaran dan pengukuran <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Kinematika gerak lurus <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Dinamika gaya <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Hukum Newton <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Usaha dan energi <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
        </li>

        <!-- Kimia -->
        <li><strong>Kimia</strong>
          <div class="copy-box"><pre>Struktur atom <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Sistem periodik unsur <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Ikatan kimia <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Stoikiometri dasar <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Larutan <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
        </li>

        <!-- Biologi -->
        <li><strong>Biologi</strong>
          <div class="copy-box"><pre>Keanekaragaman hayati <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Struktur dan fungsi sel <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Sistem gerak manusia <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Sistem pernapasan <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Sistem pencernaan <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
        </li>

        <!-- Sejarah -->
        <li><strong>Sejarah</strong>
          <div class="copy-box"><pre>Peradaban awal dunia <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Kerajaan Hindu-Buddha di Indonesia <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Kerajaan Islam di Indonesia <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Kolonialisme dan imperialisme <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
        </li>

        <!-- PPKn -->
        <li><strong>PPKn</strong>
          <div class="copy-box"><pre>Pancasila dalam kehidupan sehari-hari <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>UUD 1945 dan praktiknya <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Sistem hukum dan HAM <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
        </li>

        <!-- Informatika -->
        <li><strong>Informatika</strong>
          <div class="copy-box"><pre>Algoritma dasar <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Dasar pemrograman <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Jaringan komputer <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Etika digital <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
        </li>

        <!-- Seni Budaya -->
        <li><strong>Seni Budaya</strong>
          <div class="copy-box"><pre>Seni rupa daerah <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Seni musik tradisional <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Seni tari daerah <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
        </li>

        <!-- PJOK -->
        <li><strong>PJOK</strong>
          <div class="copy-box"><pre>Kebugaran jasmani <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Permainan bola besar <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
          <div class="copy-box"><pre>Renang dasar <button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
        </li>

      </ul>
    </li>
    <!-- ================= FASE F (Kelas XI-XII) ================= -->
<li>
  <button class="dropdown-btn">Fase F (Kelas XI-XII)</button>
  <ul class="dropdown-container">

    <!-- Bahasa Indonesia -->
    <li><b>Bahasa Indonesia</b>
      <div class="copy-box"><pre>Menganalisis teks ceramah<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Menyusun teks editorial<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Menganalisis teks kritik sastra<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Bahasa Inggris -->
    <li><b>Bahasa Inggris</b>
      <div class="copy-box"><pre>Discussion texts<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Review texts<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Public speaking & debate<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Matematika Lanjutan -->
    <li><b>Matematika Lanjutan</b>
      <div class="copy-box"><pre>Limit fungsi & turunan<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Integral tak tentu & tentu<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Matriks & transformasi linear<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Trigonometri lanjutan<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Fisika Lanjutan -->
    <li><b>Fisika Lanjutan</b>
      <div class="copy-box"><pre>Fluida dinamis<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Gelombang elektromagnetik<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Listrik arus bolak-balik (AC)<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Fisika modern (relativitas, kuantum)<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Kimia Lanjutan -->
    <li><b>Kimia Lanjutan</b>
      <div class="copy-box"><pre>Termokimia<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Kinetika reaksi<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Kesetimbangan kimia<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Kimia organik (alkohol, ester, polimer)<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Biologi Lanjutan -->
    <li><b>Biologi Lanjutan</b>
      <div class="copy-box"><pre>Sistem koordinasi (saraf & hormon)<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Bioteknologi<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Genetika & hereditas<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Ekologi & lingkungan<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Sejarah -->
    <li><b>Sejarah</b>
      <div class="copy-box"><pre>Sejarah dunia abad 19â€“20<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Pergerakan nasional Indonesia<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Dinamika politik pasca kemerdekaan<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Sosiologi -->
    <li><b>Sosiologi</b>
      <div class="copy-box"><pre>Perubahan sosial & globalisasi<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Dinamika kelompok sosial<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Masalah sosial & solusinya<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Ekonomi -->
    <li><b>Ekonomi</b>
      <div class="copy-box"><pre>Permintaan, penawaran, dan harga<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Moneter & perbankan<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Perdagangan internasional<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Geografi -->
    <li><b>Geografi</b>
      <div class="copy-box"><pre>Dinamika atmosfer & hidrosfer<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Keragaman budaya & interaksi wilayah<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Mitigasi bencana alam<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Pendidikan Pancasila -->
    <li><b>Pendidikan Pancasila</b>
      <div class="copy-box"><pre>Pancasila dalam kehidupan berbangsa<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>UUD 1945 & demokrasi<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Hak & kewajiban warga negara<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Informatika -->
    <li><b>Informatika</b>
      <div class="copy-box"><pre>Pemrograman berorientasi objek<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Basis data relasional<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Kecerdasan buatan dasar<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- Kewirausahaan -->
    <li><b>Kewirausahaan</b>
      <div class="copy-box"><pre>Konsep bisnis & peluang usaha<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Perencanaan usaha<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Manajemen keuangan bisnis<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

    <!-- PJOK -->
    <li><b>PJOK</b>
      <div class="copy-box"><pre>Permainan bola besar & kecil<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Aktivitas kebugaran jasmani<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
      <div class="copy-box"><pre>Pencegahan cedera & P3K<button class="copy-btn" onclick="copyText(this)"><i class="fa-regular fa-clone"></i></button></pre></div>
    </li>

  </ul>
</li>
</div>




            
             <!-- Overlay -->
        <div id="overlay" class="overlay" onclick="closeSidebar()"></div>
  
  
        <img src="<?php echo $_SESSION['user']['photoURL']; ?>" width="30" style="border-radius: 50%;" class="profile-pic" alt="User">
        <div class="dropdown-menu">
                <a href="logout.php">Logout</a>
        </header>    
    </div>
    <!-- chat box -->
   <div id="chatbox">
    
    <div id="messages"></div>
    <div id="inputArea">
      <input type="text" id="userInput" placeholder="Ketik pesan..." onkeydown="if(event.key==='Enter') sendMessage()">
      <button onclick="sendMessage()">Kirim</button>
    </div>
  </div>

<!-- end chat box -->

<script>
    const searchInput = document.getElementById("searchInput");
    const kelasSelect = document.getElementById("kelasSelect");
    const searchButton = document.getElementById("searchButton");

    // Fungsi untuk menyaring materi berdasarkan kelas dan kata kunci
    function filterMateri() {
        const keyword = searchInput.value.toLowerCase();
        const selectedKelas = kelasSelect.value;
        const cards = document.querySelectorAll(".card");

        cards.forEach(card => {
            const kelas = card.getAttribute("data-kelas");
            const content = card.textContent.toLowerCase();

            if (
                (kelas === selectedKelas || selectedKelas === "") &&
                (content.includes(keyword) || keyword === "")
            ) {
                card.style.display = "block"; // Tampilkan kartu
            } else {
                card.style.display = "none"; // Sembunyikan kartu
            }
        });
    }

    // Event listener untuk pencarian
    searchButton.addEventListener("click", filterMateri);

    // Event listener untuk perubahan pada input pencarian
    searchInput.addEventListener("input", filterMateri);

    // Event listener untuk perubahan pilihan kelas
    kelasSelect.addEventListener("change", filterMateri);
    
    // Fungsi untuk menangani tombol "Lihat Materi"
    document.querySelectorAll(".viewButton").forEach(button => {
        button.addEventListener("click", function() {
            const detailSection = this.nextElementSibling;
            if (detailSection.style.display === "none" || detailSection.style.display === "") {
                detailSection.style.display = "block"; // Tampilkan detail
                this.textContent = "Tutup Materi"; // Ubah teks tombol
            } else {
                detailSection.style.display = "none"; // Sembunyikan detail
                this.textContent = "Lihat Materi"; // Ubah teks tombol
            }
        });
    });
    function copyToClipboard(button) {
            // Cari elemen teks di dalam container yang sama dengan tombol
            const container = button.parentElement;
            const textToCopy = container.querySelector(".copyText").innerText;

            navigator.clipboard.writeText(textToCopy).then(() => {
                alert("Teks berhasil disalin: " + textToCopy);
            }).catch(err => {
                alert("Gagal menyalin teks.");
                console.error(err);
            });
        }
        // Preloader Script
        window.addEventListener("load", function() {
            const preloader = document.getElementById("preloader");
            preloader.style.opacity = "0"; // Fade out effect
            setTimeout(() => {
                preloader.style.display = "none"; // Remove preloader
            }, 500); // Adjust fade duration
        });
</script>



   

   <!-- <footer>
        &copy; EDUCONNECT. All rights reserved.
    </footer> -->
<script>
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyDFbAt043YWvZ7wwKvLnOFYYdaR8SwEgOo",
  authDomain: "educonnect-47dcb.firebaseapp.com",
  projectId: "educonnect-47dcb",
  storageBucket: "educonnect-47dcb.firebasestorage.app",
  messagingSenderId: "271361548411",
  appId: "1:271361548411:web:353562a08eabe0ba9082de",
  measurementId: "G-1R7N9T3TSJ"
};

  firebase.initializeApp(firebaseConfig);

  // Cek apakah user sudah login
  firebase.auth().signInWithPopup(provider) => {
    if (!user) {
      // Jika tidak login, arahkan ke halaman login
      window.location.href = "index.php";
    } else {
      // Jika login, tampilkan info user
      document.getElementById('user-name').innerText = user.displayName;
      document.getElementById('user-email').innerText = user.email;
      document.getElementById('user-photo').src = user.photoURL;
    }
  });

  function logout() {
    firebase.auth().signOut().then(() => {
      window.location.href = "index.php";
    });
  }
</script>

<script>
  // Inisialisasi Firebase (jika belum)
  const firebaseConfig = {
    apiKey: "API_KEY_MU",
    authDomain: "PROJECT.firebaseapp.com",
    projectId: "PROJECT",
    storageBucket: "PROJECT.appspot.com",
    messagingSenderId: "123456789",
    appId: "1:123456789:web:abcdef"
  };
  firebase.initializeApp(firebaseConfig);

  // Logout
  document.getElementById('logout-btn').addEventListener('click', function (e) {
    e.preventDefault(); // Hindari reload
    firebase.auth().signOut().then(() => {
      // Setelah logout, arahkan ke halaman login
      window.location.href = "index.html"; // atau index.php
    }).catch((error) => {
      alert("Logout gagal: " + error.message);
    });
  });
</script>

<script>
    // Script untuk toggle dropdown
document.addEventListener("DOMContentLoaded", function() {
    const profilePic = document.querySelector(".profile-pic");
    const dropdownMenu = document.querySelector(".dropdown-menu");

    profilePic.addEventListener("click", function() {
        dropdownMenu.style.display = 
            dropdownMenu.style.display === "block" ? "none" : "block";
    });

    // Klik di luar dropdown untuk menutup
    document.addEventListener("click", function(e) {
        if (!profilePic.contains(e.target) && !dropdownMenu.contains(e.target)) {
            dropdownMenu.style.display = "none";
        }
    });
});

</script>
<script>
function toggleDropdown() {
    document.getElementById("dropdownMenu").style.display =
        document.getElementById("dropdownMenu").style.display === "block" ? "none" : "block";
}

// Tutup dropdown kalau klik di luar
document.addEventListener("click", function(event) {
    const dropdown = document.querySelector(".dropdown");
    if (!dropdown.contains(event.target)) {
        document.getElementById("dropdownMenu").style.display = "none";
    }
});
</script>
<script>
    function toggleSidebar() {
      document.getElementById("sidebar").classList.toggle("active");
      document.getElementById("overlay").classList.toggle("active");
    }

    function closeSidebar() {
      document.getElementById("sidebar").classList.remove("active");
      document.getElementById("overlay").classList.remove("active");
    }
  </script>
  <script>
      // Dropdown handler
document.querySelectorAll(".dropdown-btn").forEach(btn => {
  btn.addEventListener("click", function() {
    const dropdown = this.nextElementSibling;
    dropdown.style.display = 
      dropdown.style.display === "block" ? "none" : "block";
  });
});

  </script>
 <script>
  // Fungsi copy
  function copyText(btn) {
    // Cari teks di dalam pre (kecuali tombol)
    const pre = btn.parentElement;
    const text = pre.childNodes[0].textContent.trim();

    // Salin ke clipboard
    navigator.clipboard.writeText(text).then(() => {
      // Ubah icon jadi ceklis sebentar
      btn.innerHTML = '<i class="fa-solid fa-check"></i>';
      setTimeout(() => {
        btn.innerHTML = '<i class="fa-regular fa-clone"></i>';
      }, 1000);
    }).catch(err => {
      console.error('Gagal menyalin teks: ', err);
    });
  }
</script>
<!-- skrip teks only -->
<!-- <script>
  function appendMessage(sender, text) {
    const messages = document.getElementById("messages");

    const row = document.createElement("div");
    row.className = "msg-row " + sender;

   const avatar = document.createElement("div");
avatar.className = "avatar";

// contoh pakai gambar PNG
avatar.innerHTML = sender === "bot"
  ? '<img src="logo.png" alt="Bot" class="avatar-img">'
  : '<img src="user.png" alt="User" class="avatar-img">';


    const msg = document.createElement("div");
    msg.className = "msg";

    // render markdown hanya untuk bot
    if (sender === "bot") {
      msg.innerHTML = renderMarkdown(text);
    } else {
      msg.innerText = text;
    }

    if (sender === "bot") {
      row.appendChild(avatar);
      row.appendChild(msg);
    } else {
      row.appendChild(msg);
      row.appendChild(avatar);
    }

    messages.appendChild(row);
    messages.scrollTop = messages.scrollHeight;
  }

  // Fungsi sederhana parse Markdown
  function renderMarkdown(md) {
    return md
      .replace(/\*\*(.*?)\*\*/g, "<b>$1</b>")    // **bold**
      .replace(/\*(.*?)\*/g, "<i>$1</i>")        // *italic*
      .replace(/`(.*?)`/g, "<code>$1</code>")    // `code`
      .replace(/\n/g, "<br>")                    // newline
      .replace(/^- (.*)$/gm, "• $1")             // bullet list
      .replace(/\[(.*?)\]\((.*?)\)/g, "<a href='$2' target='_blank'>$1</a>");
  }

  async function sendMessage() {
    const input = document.getElementById("userInput");
    const text = input.value.trim();
    if (!text) return;

    appendMessage("user", text);
    input.value = "";
    appendMessage("bot", "Sedang mengetik...");

    try {
      const response = await fetch("chat-gemini.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message: text })
      });

      const data = await response.json();

      // hapus placeholder "sedang mengetik..."
      const messages = document.getElementById("messages");
      messages.lastChild.remove();

      appendMessage("bot", data.reply || "(tidak ada balasan)");
    } catch (err) {
      appendMessage("bot", "⚠️ Error: " + err.message);
    }
  }
</script> -->
<!-- skrip teks only -->
<!-- skrip teks, tabel, gambar -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  function appendMessage(sender, content, type="text") {
    const messages = document.getElementById("messages");

    const row = document.createElement("div");
    row.className = "msg-row " + sender;

    const avatar = document.createElement("div");
    avatar.className = "avatar";
    avatar.innerHTML = sender === "bot"
      ? '<img src="logo.png" alt="Bot" class="avatar-img">'
      : '<img src="user.png" alt="User" class="avatar-img">';

    const msg = document.createElement("div");
    msg.className = "msg";

    // --- render berdasarkan tipe konten ---
    if (type === "text") {
      if (sender === "bot") {
        msg.innerHTML = renderMarkdown(content);
      } else {
        msg.innerText = content;
      }
    }
    else if (type === "image") {
      const img = document.createElement("img");
      img.src = content;
      img.style.maxWidth = "250px";
      img.style.display = "block";

      const link = document.createElement("a");
      link.href = content;
      link.download = "gambar.png";
      link.textContent = "⬇️ Download";
      link.style.display = "block";
      link.style.marginTop = "5px";

      msg.appendChild(img);
      msg.appendChild(link);
    }
    else if (type === "table") {
      msg.innerHTML = content; // sudah HTML table
    }
    else if (type === "chart") {
      const canvas = document.createElement("canvas");
      msg.appendChild(canvas);

      setTimeout(() => {
        const chart = new Chart(canvas, content);

        const link = document.createElement("a");
        link.textContent = "⬇️ Download Grafik";
        link.style.display = "block";
        link.style.marginTop = "5px";
        link.style.color = "#0d6efd";
        link.onclick = () => {
          const url = chart.toBase64Image();
          const a = document.createElement("a");
          a.href = url;
          a.download = "grafik.png";
          a.click();
        };
        msg.appendChild(link);
      }, 50);
    }

    if (sender === "bot") {
      row.appendChild(avatar);
      row.appendChild(msg);
    } else {
      row.appendChild(msg);
      row.appendChild(avatar);
    }

    messages.appendChild(row);
    messages.scrollTop = messages.scrollHeight;
  }

  // Markdown parser sederhana
  function renderMarkdown(md) {
    return md
      .replace(/\*\*(.*?)\*\*/g, "<b>$1</b>")
      .replace(/\*(.*?)\*/g, "<i>$1</i>")
      .replace(/`(.*?)`/g, "<code>$1</code>")
      .replace(/\n/g, "<br>")
      .replace(/^- (.*)$/gm, "• $1")
      .replace(/\[(.*?)\]\((.*?)\)/g, "<a href='$2' target='_blank'>$1</a>");
  }

  async function sendMessage() {
    const input = document.getElementById("userInput");
    const text = input.value.trim();
    if (!text) return;

    appendMessage("user", text);
    input.value = "";
    appendMessage("bot", "Sedang mengetik...");

    try {
      const response = await fetch("chat-gemini.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message: text })
      });

      const data = await response.json();

      // hapus placeholder "sedang mengetik..."
      const messages = document.getElementById("messages");
      messages.lastChild.remove();

      // --- cek apakah balasan bot punya tipe selain teks ---
      if (data.type === "image") {
        appendMessage("bot", data.url, "image");
      }
      else if (data.type === "table") {
        appendMessage("bot", data.html, "table");
      }
      else if (data.type === "chart") {
        appendMessage("bot", data.config, "chart"); // config Chart.js
      }
      else {
        appendMessage("bot", data.reply || "(tidak ada balasan)");
      }

    } catch (err) {
      appendMessage("bot", "⚠️ Error: " + err.message);
    }
  }
</script>


</body>
</html>
