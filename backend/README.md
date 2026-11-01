# LearningAssistant Gemini API — Google Cloud Run

Backend Node.js ini menjadi perantara aman antara GitHub Pages dan Gemini API. Browser mengirim Firebase ID token; backend memverifikasinya dengan Firebase Admin SDK sebelum meneruskan pertanyaan ke Gemini.

## Keamanan yang diterapkan

- Gemini API key hanya dibaca dari Secret Manager melalui GEMINI_API_KEY.
- Firebase ID token diverifikasi server-side, beserta tanda tangan dan masa berlakunya.
- CORS dibatasi ke origin GitHub Pages yang diizinkan.
- JSON body dibatasi 20 KB dan pesan 8.000 karakter.
- Pembatasan awal 15 permintaan/menit per pengguna per instance.
- Container berjalan sebagai user non-root.
- Tidak memerlukan file JSON service account. Cloud Run menggunakan Application Default Credentials dari service account yang ditempelkan ke service.

Catatan: rate limit dalam memori berlaku per instance, bukan kuota global. Batas maksimum instance dan kuota Gemini juga perlu diatur. Untuk kuota global lintas instance, gunakan penyimpanan bersama seperti Firestore atau Memorystore.

## Prasyarat

- Google Cloud project: educonnect-47dcb (sesuaikan jika project backend berbeda).
- Google Cloud CLI (gcloud) sudah terpasang dan login.
- Billing aktif pada project.
- API key Gemini BARU yang sudah dibuat setelah mencabut key lama.
- Firebase Authentication Google sign-in aktif pada project Firebase yang sama.

Jangan memasukkan API key ke GitHub, file JavaScript, Dockerfile, atau chat. Jangan membuat atau mengunduh service-account JSON key.

## 1. Pilih project dan aktifkan layanan

Jalankan dari Terminal Mac:

```bash
gcloud auth login
gcloud config set project educonnect-47dcb
gcloud services enable run.googleapis.com cloudbuild.googleapis.com artifactregistry.googleapis.com secretmanager.googleapis.com
```

## 2. Buat runtime service account

```bash
gcloud iam service-accounts create learningassistant-runtime \
  --display-name="LearningAssistant Cloud Run runtime"
```

Alamat service account:

```text
learningassistant-runtime@educonnect-47dcb.iam.gserviceaccount.com
```

## 3. Simpan Gemini API key baru di Secret Manager

Buat secret (hanya pertama kali):

```bash
gcloud secrets create gemini-api-key --replication-policy=automatic
```

Masukkan API key baru secara tersembunyi di terminal, lalu simpan sebagai versi secret:

```bash
read -s "GEMINI_API_KEY?Masukkan Gemini API key baru: "
printf '\n'
printf '%s' "$GEMINI_API_KEY" | gcloud secrets versions add gemini-api-key --data-file=-
unset GEMINI_API_KEY
```

Jika secret sudah ada, jangan jalankan perintah create lagi; langsung tambahkan versi baru dengan perintah versions add.

Berikan akses secret hanya kepada runtime service account:

```bash
gcloud secrets add-iam-policy-binding gemini-api-key \
  --member="serviceAccount:learningassistant-runtime@educonnect-47dcb.iam.gserviceaccount.com" \
  --role="roles/secretmanager.secretAccessor"
```

## 4. Deploy backend ke Cloud Run

Dari folder utama repository (folder yang berisi folder backend/):

```bash
gcloud run deploy learningassistant-api \
  --source ./backend \
  --region asia-southeast2 \
  --allow-unauthenticated \
  --service-account learningassistant-runtime@educonnect-47dcb.iam.gserviceaccount.com \
  --set-env-vars FIREBASE_PROJECT_ID=educonnect-47dcb,ALLOWED_ORIGINS=https://arifulamar.github.io,GEMINI_MODEL=gemini-3.8-flash \
  --set-secrets GEMINI_API_KEY=gemini-api-key:1 \
  --max-instances 3 \
  --concurrency 20 \
  --memory 512Mi \
  --timeout 60
```

Cloud Run dibuat dapat diakses secara jaringan publik supaya browser GitHub Pages dapat menghubunginya. Ini TIDAK berarti endpoint chat terbuka: setiap permintaan POST /api/chat tetap harus membawa Firebase ID token yang valid. Jangan menghapus middleware verifikasi token.

Setelah deploy, ambil URL service:

```bash
gcloud run services describe learningassistant-api --region asia-southeast2 --format='value(status.url)'
```

Tes health endpoint dengan membuka URL service ditambah /health; hasil yang diharapkan: {"status":"ok"}.

## 5. Hubungkan GitHub Pages

Buka file api-config.js di root repository dan ganti nilai placeholder:

```js
window.LEARNING_ASSISTANT_API_URL = "https://URL-SERVICE-ANDA.run.app";
```

Jangan menambahkan /api/chat di nilai tersebut; frontend menambahkannya sendiri. Commit dan push perubahan, lalu tunggu GitHub Pages selesai deploy.

Origin GitHub Pages untuk repository project ini adalah https://arifulamar.github.io (tanpa path /LearningAssistant). Jika memakai custom domain, tambahkan origin custom domain ke ALLOWED_ORIGINS dan deploy ulang.

## 6. Pengujian

1. Buka https://arifulamar.github.io/LearningAssistant/ dan login dengan Google.
2. Buka dashboard dan kirim pertanyaan sederhana.
3. Pastikan respons Gemini tampil.
4. Uji endpoint tanpa token: harus menerima HTTP 401.
5. Pastikan tidak ada API key di tab Network browser, file JavaScript, atau source GitHub.

## 7. Rotasi secret

Buat API key pengganti di Google AI Studio / Google Cloud, lalu tambahkan versi secret baru. Setelah deploy ulang service dengan versi secret baru, cabut key lama. Jangan mengaktifkan kembali key yang pernah terekspos.

## Penting

- GitHub Pages hanya host statis; jangan mengandalkan file PHP untuk menjalankan backend.
- File chat-gemini.php lama bukan backend aktif untuk GitHub Pages.
- API key Firebase Web di main.js adalah konfigurasi client; batasi API terkait dan lindungi data melalui Firebase Security Rules. Jangan samakan Firebase Web API key dengan Gemini API key rahasia.
- Periksa billing, quota Gemini, dan log Cloud Run secara berkala.