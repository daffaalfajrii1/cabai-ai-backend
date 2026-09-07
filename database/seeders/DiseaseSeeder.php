<?php

namespace Database\Seeders;

use App\Models\Disease;
use Illuminate\Database\Seeder;

class DiseaseSeeder extends Seeder
{
    public function run(): void
    {
        $diseases = [
            [
                'ai_class_name' => 'Bacterial Spot',
                'slug' => 'bercak-bakteri',
                'name' => 'Bercak Bakteri',
                'scientific_name' => null,
                'description' => 'Penyakit bakteri yang dapat menimbulkan bercak pada daun cabai, terutama pada kondisi lembap.',
                'symptoms' => 'Bercak kecil tidak beraturan pada daun, daun dapat menguning, dan bercak bisa meluas ketika serangan berat.',
                'cause' => 'Umumnya dipicu bakteri patogen yang berkembang pada kelembapan tinggi, percikan air, dan sanitasi kebun yang kurang baik.',
                'treatment' => 'Pisahkan bagian tanaman yang parah, kurangi kelembapan berlebih, perbaiki sirkulasi udara, dan konsultasikan pengendalian lanjutan dengan penyuluh setempat.',
                'prevention' => 'Gunakan benih sehat, hindari penyiraman langsung ke daun, jaga jarak tanam, dan bersihkan sisa tanaman sakit.',
                'is_healthy' => false,
            ],
            [
                'ai_class_name' => 'Cercospora Leaf Spot',
                'slug' => 'bercak-daun-cercospora',
                'name' => 'Bercak Daun Cercospora',
                'scientific_name' => 'Cercospora spp.',
                'description' => 'Penyakit bercak daun yang biasanya berkembang pada cuaca lembap dan dapat mengurangi area daun sehat.',
                'symptoms' => 'Bercak bulat hingga tidak beraturan dengan pusat lebih pucat, daun menguning, dan daun tua dapat rontok.',
                'cause' => 'Disebabkan jamur Cercospora yang menyebar melalui percikan air, angin, atau sisa tanaman terinfeksi.',
                'treatment' => 'Pangkas daun yang parah, tingkatkan sanitasi lahan, dan gunakan pengendalian yang sesuai rekomendasi petugas pertanian.',
                'prevention' => 'Jaga sirkulasi udara, hindari kelembapan berlebih, rotasi tanaman, dan buang sisa tanaman sakit.',
                'is_healthy' => false,
            ],
            [
                'ai_class_name' => 'Curl Virus',
                'slug' => 'virus-daun-keriting',
                'name' => 'Virus Daun Keriting',
                'scientific_name' => null,
                'description' => 'Gangguan virus yang membuat daun cabai mengeriting dan pertumbuhan tanaman melemah.',
                'symptoms' => 'Daun mengeriting, ukuran daun mengecil, warna daun tidak merata, dan tanaman tampak kerdil.',
                'cause' => 'Virus sering menyebar melalui serangga vektor seperti kutu kebul atau bahan tanam yang sudah terinfeksi.',
                'treatment' => 'Tanaman yang berat sebaiknya dipisahkan, kendalikan vektor secara terpadu, dan minta rekomendasi teknis dari penyuluh.',
                'prevention' => 'Gunakan bibit sehat, pantau serangga vektor, pasang perangkap bila sesuai, dan bersihkan gulma inang di sekitar lahan.',
                'is_healthy' => false,
            ],
            [
                'ai_class_name' => 'Healthy Leaf',
                'slug' => 'daun-sehat',
                'name' => 'Daun Sehat',
                'scientific_name' => null,
                'description' => 'Daun tidak menunjukkan gejala penyakit visual yang jelas pada foto yang dianalisis.',
                'symptoms' => 'Warna daun relatif merata, bentuk daun normal, dan tidak tampak bercak penyakit dominan.',
                'cause' => 'Tidak ada penyebab penyakit yang terindikasi dari hasil deteksi visual.',
                'treatment' => 'Lanjutkan perawatan rutin, pemantauan berkala, dan pemupukan sesuai kebutuhan tanaman.',
                'prevention' => 'Pertahankan sanitasi kebun, penyiraman yang baik, sirkulasi udara, dan pemantauan hama penyakit.',
                'is_healthy' => true,
            ],
            [
                'ai_class_name' => 'Nutrition Deficiency',
                'slug' => 'kekurangan-nutrisi',
                'name' => 'Kekurangan Nutrisi',
                'scientific_name' => null,
                'description' => 'Kondisi tanaman yang menunjukkan tanda kemungkinan kekurangan unsur hara, bukan diagnosis laboratorium.',
                'symptoms' => 'Daun dapat tampak pucat, menguning, pertumbuhan lambat, atau pola warna tidak merata.',
                'cause' => 'Dapat berkaitan dengan ketersediaan hara tanah, pH, kelembapan, atau gangguan akar.',
                'treatment' => 'Periksa kondisi tanah dan riwayat pemupukan, lalu sesuaikan nutrisi berdasarkan rekomendasi agronom atau penyuluh.',
                'prevention' => 'Gunakan media tanam yang baik, pemupukan berimbang, drainase cukup, dan evaluasi kondisi tanaman secara berkala.',
                'is_healthy' => false,
            ],
            [
                'ai_class_name' => 'White spot',
                'slug' => 'bercak-putih',
                'name' => 'Bercak Putih',
                'scientific_name' => null,
                'description' => 'Gejala bercak putih pada daun yang dapat berkaitan dengan beberapa faktor, sehingga perlu pemeriksaan lapangan.',
                'symptoms' => 'Bercak berwarna putih atau pucat pada permukaan daun, kadang disertai perubahan warna di area sekitar bercak.',
                'cause' => 'Dapat dipengaruhi infeksi patogen, kerusakan jaringan, atau tekanan lingkungan tertentu.',
                'treatment' => 'Amati penyebaran gejala, pisahkan daun yang berat bila perlu, dan konsultasikan pengendalian jika gejala meluas.',
                'prevention' => 'Jaga kebersihan kebun, hindari kelembapan berlebih, dan lakukan pemantauan rutin pada daun muda maupun tua.',
                'is_healthy' => false,
            ],
        ];

        foreach ($diseases as $disease) {
            Disease::updateOrCreate(
                ['ai_class_name' => $disease['ai_class_name']],
                $disease + ['is_active' => true]
            );
        }
    }
}
