PRD: Sistem Manajemen Vendor Transportasi (SIMVENTRA)
Versi: 1.0 Tanggal: 16 September 2026 Pemilik Produk: [Diki Romadoni] Tech Stack: Laravel 13, MySQL, (opsional: Livewire/Inertia + Vue/React untuk frontend, Redis untuk queue/cache)

1. Latar Belakang
Perusahaan menyediakan jasa transportasi/logistik dan akan menjalani Audit Vendor (mengacu pada formulir PTT.FM.043.02) yang menilai kesiapan pada 9 area: Quality Management System, Personnel, Document Management, Sanitation & Hygiene, Vehicle Maintenance, Tracking System, Business Continuity Planning, Security, dan Routing.

Saat ini proses-proses tersebut sebagian besar manual (Excel, WhatsApp, kertas), sehingga sulit dibuktikan konsistensinya saat audit dan berisiko human error. Dibutuhkan sistem informasi terpusat agar seluruh data terdokumentasi, mudah ditelusuri (auditable), dan mendukung operasional harian.

2. Tujuan
Menyediakan sistem pencatatan terpusat untuk personel, kendaraan, dokumen, pelatihan, insiden, dan pengiriman.
Menyediakan bukti digital (log, riwayat, timestamp) yang siap ditunjukkan saat audit vendor.
Mendukung pemantauan kendaraan secara real-time (tracking & panic button).
Mengurangi risiko keterlambatan perpanjangan dokumen (SIM, STNK, KIR) melalui reminder otomatis.
Meningkatkan keamanan pengiriman melalui pencatatan rute, insiden, dan verifikasi sopir.
3. Target Pengguna (Roles)
Role	Deskripsi Akses
Super Admin	Akses penuh ke seluruh modul & konfigurasi sistem
Manajemen/QMS Reviewer	Melihat dashboard, laporan, hasil review berkala, komplain pelanggan
HR/Personalia	Mengelola data karyawan/sopir, rekrutmen, pelatihan
Fleet/Control Tower Officer	Memantau kendaraan & sopir secara real-time, menerima alert
Maintenance Officer	Mengelola jadwal & riwayat perawatan kendaraan
Security Officer	Mengelola insiden keamanan, catatan pelanggaran, panic button alert
Sopir (mobile/limited access)	Update status pengiriman, lapor insiden, cek jadwal pelatihan sendiri
Auditor (read-only, opsional)	Akses view-only ke laporan & dokumen untuk kebutuhan audit
4. Ruang Lingkup Modul
Modul dipetakan langsung dari kategori checklist audit agar traceable.

4.1 Modul Quality Management System (QMS)
Struktur organisasi digital (chart) dengan role & tanggung jawab.
Modul "Management Review": jadwal review berkala, notulen, tindak lanjut (action item + status: open/in progress/closed).
Modul komplain pelanggan: input komplain → assign PIC → tracking status → resolusi.
Manajemen sertifikasi halal (upload, tanggal berlaku, reminder perpanjangan).
4.2 Modul Personnel (HRIS ringan)
Database karyawan & sopir: data diri, riwayat kerja, hasil background check, catatan kriminal (jika ada), kontak kerabat terdekat.
Modul rekrutmen: checklist verifikasi (SKCK, referensi kerja, kualifikasi) dengan status per kandidat.
Modul pelatihan: jadwal (induksi, berkala, refresh), peserta (karyawan tetap/kontrak/mitra), materi, daftar hadir, hasil evaluasi efektivitas.
Manajemen ID card (nomor, masa berlaku, status aktif/nonaktif).
4.3 Modul Document Management
Repository dokumen legal kendaraan (STNK, KIR, izin trayek) & sopir (SIM, kontrak kerja).
Reminder otomatis (email/notifikasi) H-30/H-14/H-7 sebelum dokumen kadaluarsa.
Kebijakan retensi dokumen (auto-archive/soft delete sesuai periode retensi yang dikonfigurasi).
Log setiap aktivitas operasional & perbaikan (audit trail).
4.4 Modul Sanitation & Hygiene
Checklist kebersihan kendaraan (jadwal, PIC, metode) diisi via form digital per kendaraan/trip.
Riwayat kebersihan per kendaraan (dashboard status: bersih/perlu tindakan).
Flag kendaraan "dedicated halal" vs non-halal.
Form pelaporan insiden kontaminasi + tindak lanjut.
4.5 Modul Vehicle & Equipment Maintenance
Master data kendaraan (jenis, kapasitas, tahun, status aktif).
Jadwal preventive maintenance (berbasis waktu/km) dengan reminder.
Riwayat perbaikan (tanggal, jenis kerusakan, biaya, bengkel, PIC).
Checklist maintenance digital.
4.6 Modul Tracking System
Integrasi GPS tracker (via API pihak ketiga/hardware IoT) untuk posisi real-time kendaraan.
Dashboard peta (live map) untuk Control Tower.
Riwayat rute perjalanan per pengiriman.
Geofencing (opsional) untuk deteksi penyimpangan rute.
4.7 Modul Business Continuity Planning (BCP)
Repository dokumen BCP (rencana tanggap darurat: bencana, kecelakaan, pencurian).
Modul aktivasi insiden kritikal: log kejadian, eskalasi, status penanganan.
4.8 Modul Security
SOP digital (penanganan pencurian, larangan menumpangkan orang).
Modul panic button: integrasi hardware → trigger alert ke Control Tower + opsi notifikasi ke kontak polisi/rekanan keamanan.
Log tes narkoba/alkohol sopir (jadwal & hasil, rekrutmen/random/berkala).
Program loyalitas sopir: tracking masa kerja, poin/insentif (opsional gamifikasi sederhana).
Riwayat pelanggaran lalu lintas & insiden per sopir (terhubung ke modul Personnel).
Log pelaporan upaya suap/ancaman dari sopir.
4.9 Modul Routing
Master data rute standar & titik rawan (blind spot) yang harus dihindari.
Monitoring waktu transit vs rencana; alert jika keterlambatan melebihi threshold.
Log penyimpangan rute/keterlambatan dengan keterangan sopir.
4.10 Modul Dashboard & Pelaporan (Cross-cutting)
Dashboard eksekutif: ringkasan kepatuhan per kategori audit (skor kesiapan audit self-assessment).
Export laporan ke Excel/PDF (untuk kebutuhan audit fisik).
Fitur "Audit Mode": generate paket bukti dokumen otomatis per kategori (A–I) sesuai checklist PTT.FM.043.02.
5. Functional Requirements (Ringkasan Prioritas)
ID	Requirement	Prioritas
FR-01	Autentikasi & manajemen role/permission (Laravel Breeze/Fortify + Spatie Permission)	Must Have
FR-02	CRUD data master: karyawan, sopir, kendaraan, dokumen	Must Have
FR-03	Reminder otomatis expiry dokumen (queue job harian + notifikasi email/WA)	Must Have
FR-04	Modul pelatihan dengan upload bukti (file/foto) & status evaluasi	Must Have
FR-05	Checklist digital (kebersihan, maintenance) dengan riwayat & foto bukti	Must Have
FR-06	Log insiden (keamanan, kontaminasi, kecelakaan) dengan alur status	Must Have
FR-07	Integrasi GPS tracking real-time (API pihak ketiga)	Must Have
FR-08	Panic button alert (webhook dari device → notifikasi real-time)	Should Have
FR-09	Dashboard kepatuhan audit & export laporan	Must Have
FR-10	Audit trail/log aktivitas seluruh perubahan data penting	Must Have
FR-11	Modul komplain pelanggan & tindak lanjut	Should Have
FR-12	Geofencing & deteksi penyimpangan rute otomatis	Could Have
FR-13	Program loyalitas sopir (poin/reward)	Could Have
FR-14	Aplikasi mobile ringan untuk sopir (PWA)	Should Have
6. Non-Functional Requirements
Keamanan data: enkripsi data sensitif (data pribadi sopir, catatan kriminal) sesuai UU PDP; role-based access control ketat.
Auditability: semua perubahan data penting tercatat di tabel audit log (siapa, kapan, apa yang diubah) — gunakan package seperti spatie/laravel-activitylog.
Ketersediaan: target uptime 99% untuk modul tracking & panic button (fitur kritikal keselamatan).
Skalabilitas: arsitektur mendukung penambahan jumlah kendaraan/sopir tanpa perombakan besar (queue-based processing untuk notifikasi & GPS ingestion).
Kepatuhan: desain data model mengacu langsung pada struktur checklist PTT.FM.043.02 agar mapping laporan audit mudah.
Localization: Bahasa Indonesia sebagai bahasa utama UI.
7. Arsitektur & Tech Stack
Backend: Laravel 13 (PHP 8.3+)
Database: MySQL 8.x
Queue/Job: Laravel Queue (database/Redis driver) untuk reminder & notifikasi terjadwal, gunakan Laravel Scheduler untuk cron job harian.
Autentikasi & Otorisasi: Laravel Fortify/Breeze + spatie/laravel-permission untuk role & permission granular.
Audit log: spatie/laravel-activitylog.
Notifikasi: Laravel Notification (Email via SMTP/Mailgun; WhatsApp via API pihak ketiga seperti Fonnte/Wablas jika dibutuhkan).
Frontend: Laravel Blade + Livewire (rekomendasi untuk kecepatan development internal tool) atau Inertia.js + Vue/React bila butuh UI lebih kaya (peta real-time, dashboard interaktif).
Peta/Tracking: Leaflet.js/Google Maps API untuk visualisasi peta; integrasi API GPS vendor hardware (mis. via webhook/polling).
File Storage: Laravel Filesystem (local/S3-compatible) untuk dokumen & foto bukti.
Export: maatwebsite/excel untuk Excel, barryvdh/laravel-dompdf atau spatie/laravel-pdf untuk PDF laporan.
Testing: PestPHP/PHPUnit untuk unit & feature test terutama modul reminder & audit trail.
8. Gambaran Skema Database (High-Level)
Entitas utama (disederhanakan, belum termasuk kolom lengkap):

users, roles, permissions
employees (karyawan/sopir), employee_backgrounds, employee_relatives
trainings, training_sessions, training_participants, training_evaluations
vehicles, vehicle_documents, vehicle_maintenance_schedules, vehicle_maintenance_logs
driver_documents (SIM, dsb.)
cleaning_checklists, cleaning_logs
incidents (tipe: keamanan/kontaminasi/kecelakaan), incident_followups
shipments, shipment_routes, shipment_tracking_logs
panic_alerts
customer_complaints, complaint_followups
management_reviews, review_action_items
bcp_documents
audit_logs (via activitylog package)
document_expiry_reminders (atau kolom expiry_date + scheduled job generik)
9. Roadmap Implementasi (Fase)
Fase	Fokus	Estimasi
Fase 1 – Fondasi	Auth, role/permission, master data (karyawan, kendaraan, dokumen), reminder expiry dokumen	4–6 minggu
Fase 2 – Operasional Inti	Modul pelatihan, checklist kebersihan & maintenance, log insiden	4–6 minggu
Fase 3 – Tracking & Keamanan	Integrasi GPS, panic button, dashboard peta	4–8 minggu (tergantung vendor hardware)
Fase 4 – Governance	QMS (management review, komplain), BCP, dashboard kepatuhan audit, export laporan	3–4 minggu
Fase 5 – Enhancement	Mobile PWA sopir, geofencing, program loyalitas	Opsional, sesuai kebutuhan
10. Success Metrics
100% dokumen kendaraan/sopir memiliki status masa berlaku terpantau (tidak ada dokumen expired tanpa reminder).
Waktu penyiapan bukti audit dari hitungan hari menjadi < 1 jam (via fitur export "Audit Mode").
Waktu respons terhadap panic alert < 5 menit dari trigger ke notifikasi diterima Control Tower.
Skor kesiapan audit internal (self-assessment dashboard) meningkat secara terukur tiap kuartal.
11. Risiko & Mitigasi
Risiko	Mitigasi
Ketergantungan pada vendor hardware GPS/panic button	Pilih vendor dengan API terbuka & SLA jelas; desain integrasi modular
Adopsi rendah dari sopir (teknologi baru)	Sediakan interface mobile sederhana, pelatihan penggunaan sistem
Data pribadi sensitif (kriminal, kesehatan)	Terapkan enkripsi & access control ketat, patuhi UU PDP
Beban development jika semua fitur dibangun sekaligus	Ikuti roadmap bertahap (MVP dulu di Fase 1–2)
12. Lampiran
Referensi: Formulir PTT.FM.043.02 – Daftar Periksa Audit Vendor.
Setiap requirement pada dokumen ini dapat ditelusuri balik ke poin checklist (A.1–I.3) untuk memastikan sistem yang dibangun benar-benar mendukung kelulusan audit.
Dokumen ini adalah draf awal (v1.0) dan perlu direview bersama tim manajemen, HR, dan IT sebelum masuk tahap development.