<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            // MENU BIASA
            [
                'name' => 'Bakmi Godog', 
                'description' => 'Bakmi rebus tradisional khas Yogyakarta yang dimasak menggunakan wajan baja di atas anglo arang. Disajikan dengan kuah kaldu ayam kampung kental yang gurih alami, berpadu sempurna dengan suwiran ayam, potongan kol, sawi hijau, tomat, taburan bawang goreng, seledri segar, dan telur bebek orek.', 
                'price' => 27000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/bakmi-godog.jpg'
            ],
            [
                'name' => 'Bakmi Goreng', 
                'description' => 'Bakmi goreng gurih manis yang dimasak dengan teknik tradisional di atas api arang. Mi kuning tebal dibalut bumbu racikan bawang putih, kemiri, kecap manis berkualitas, serta dilengkapi sayuran segar, telur bebek, dan suwiran daging ayam kampung yang melimpah.', 
                'price' => 27000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/bakmi-goreng.jpg'
            ],
            [
                'name' => 'Bakmi Goreng Nyemek', 
                'description' => 'Menu favorit perpaduan antara bakmi goreng dan rebus. Mi kuning tebal dimasak dengan sedikit kuah kental gurih yang kaya rempah, memberikan tekstur basah yang lezat, pekat, dan bumbu yang meresap sempurna hingga ke tiap helai mi.', 
                'price' => 27000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/bakmi-goreng-nyemek.jpg'
            ],
            [
                'name' => 'Bihun Godog', 
                'description' => 'Bihun beras lembut pilihan yang direbus di dalam kuah kaldu ayam kampung kental yang harum. Kelezatannya diperkaya dengan racikan bumbu jawa kuno, telur orek, potongan ayam suwir, kol, sawi segar, serta disajikan hangat berasap dengan aroma khas anglo arang.', 
                'price' => 27000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/bihun-godog.jpg'
            ],
            [
                'name' => 'Bihun Goreng', 
                'description' => 'Bihun beras yang digoreng kering dengan bumbu kecap manis, kemiri, dan bawang putih beraroma smokey khas panggangan arang. Dilengkapi dengan sayuran segar, telur orak-arik, dan suwiran ayam kampung yang memberikan cita rasa gurih manis nan autentik.', 
                'price' => 27000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/bihun-goreng.jpg'
            ],
            [
                'name' => 'Nasi Goreng Biasa', 
                'description' => 'Nasi goreng khas Jawa yang dimasak tanpa saus tomat, melainkan mengandalkan racikan bumbu ulek bawang, kemiri, terasi, dan kecap manis. Dimasak di atas api arang besar menghasilkan aroma gosong magis (smokey) yang berpadu dengan telur dan suwiran ayam.', 
                'price' => 27000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/nasi-goreng-biasa.jpg'
            ],
            [
                'name' => 'Nasi Goreng Magelang', 
                'description' => 'Hidangan karbohidrat ganda yang memuaskan, memadukan nasi putih dengan mi kuning khas Jawa. Dimasak bersama bumbu halus tradisional, kecap manis, kol, telur, dan suwiran ayam kampung di atas wajan anglo arang yang melegenda.', 
                'price' => 27000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/nasi-goreng-magelang.jpg'
            ],
            [
                'name' => 'Rica-Rica Ayam Kampung 1 ekor', 
                'description' => 'Satu ekor utuh ayam kampung muda yang dipotong-potong dan dimasak dengan bumbu rica-rica super pedas khas Mas Agus. Kuahnya kental kemerahan penuh dengan ulekan cabai, bawang, daun jeruk, dan serai yang meresap hingga ke dalam serat daging ayam yang empuk.', 
                'price' => 135000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/rica-rica-ayam-kampung-1-ekor.jpg'
            ],
            [
                'name' => 'Rica-Rica Ayam Kampung ½ ekor', 
                'description' => 'Setengah ekor ayam kampung pilihan yang diolah dengan bumbu rica-rica pedas mampus beraroma segar. Kombinasi rempah daun kunyit, kemangi, serai, dan daun jeruk berpadu dengan daging ayam kampung yang gurih, pas untuk dinikmati bersama nasi hangat.', 
                'price' => 68000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/rica-rica-ayam-kampung-half-ekor.jpg'
            ],
            [
                'name' => 'Rica-Rica Bebek 1 ekor', 
                'description' => 'Satu ekor bebek utuh yang diolah secara khusus agar dagingnya super empuk dan tidak amis, kemudian dimasak dalam balutan bumbu rica-rica tradisional pedas menyengat. Sensasi gurih lemak bebek berpadu sempurna dengan pedasnya rempah daun.', 
                'price' => 115000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/rica-rica-bebek-kampung-1-ekor.jpg'
            ],
            [
                'name' => 'Rica-Rica Bebek ½ ekor', 
                'description' => 'Setengah ekor bebek empuk bumbu rica pedas gurih. Daging bebek yang juicy meresap dengan tumisan cabai rawit merah, bawang merah, bawang putih, dan aroma rempah daun yang harum, siap memanjakan lidah para pecinta kuliner pedas.', 
                'price' => 58000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/rica-rica-bebek-kampung-half-ekor.jpg'
            ],
            [
                'name' => 'Rica-Rica Tulangan', 
                'description' => 'Potongan tulang-tulang muda ayam kampung yang masih memiliki sisa-sisa daging manis melekat. Dimasak dengan bumbu rica pedas kental, memberikan sensasi kenikmatan tersendiri saat menyesap bumbu rempah langsung dari sela-sela tulangnya.', 
                'price' => 27000, 
                'category' => 'Menu Biasa', 
                'is_available' => true,
                'image_path' => 'img-bakmi/rica-rica-tulangan.jpg'
            ],

            // MENU SPECIAL
            [
                'name' => 'Bakmi Godog Kepala', 
                'description' => 'Sajian premium Bakmi Godog legendaris dengan tambahan topping utuh kepala ayam kampung yang gurih. Kepala ayam dimasak lama di dalam kaldu sehingga tekstur kulit dan dagingnya sangat empuk, menambah kedalaman rasa kuah mi rebus Anda.', 
                'price' => 35000, 
                'category' => 'Menu Spesial', 
                'is_available' => true,
                'image_path' => 'img-bakmi/bakmi-godog-kepala.jpg'
            ],
            [
                'name' => 'Bakmi Goreng Sayap', 
                'description' => 'Kelembutan sayap ayam kampung pilihan dipadukan dengan gurih manisnya Bakmi Goreng khas anglo arang. Tekstur sayap ayam yang juicy menjadi jodoh yang sempurna bagi mi goreng rempah yang beraroma smokey.', 
                'price' => 35000, 
                'category' => 'Menu Spesial', 
                'is_available' => true,
                'image_path' => 'img-bakmi/bakmi-goreng-sayap.jpg'
            ],
            [
                'name' => 'Bihun Godog Ati Ampela', 
                'description' => 'Bihun Godog berkuah kaldu ayam pekat yang disajikan mewah bersama potongan ati dan ampela ayam kampung segar. Dimasak dadakan di atas tungku arang, menghasilkan cita rasa jeroan yang gurih tanpa bau amis sedikit pun.', 
                'price' => 35000, 
                'category' => 'Menu Spesial', 
                'is_available' => true,
                'image_path' => 'img-bakmi/bihun-godog-ati-ampela.jpg'
            ],
            [
                'name' => 'Bihun Goreng Brutu', 
                'description' => 'Menu spesial terfavorit para pencinta kuliner. Bihun beras goreng kecap arang yang gurih, disajikan dengan topping brutu (pantat ayam) yang terkenal sangat lembut, juicy, lemaknya meleleh, dan kaya akan rasa gurih.', 
                'price' => 35000, 
                'category' => 'Menu Spesial', 
                'is_available' => true,
                'image_path' => 'img-bakmi/bihun-goreng-brutu.jpg'
            ],
            [
                'name' => 'Nasi Goreng Biasa Uritan', 
                'description' => 'Nasi goreng Jawa arang otentik yang disajikan bersama topping uritan (telur muda ayam yang belum bertelur). Tekstur uritan yang kenyal gurih berpadu dengan gurih manisnya nasi goreng rempah menciptakan kombinasi tekstur yang unik di mulut.', 
                'price' => 35000, 
                'category' => 'Menu Spesial', 
                'is_available' => true,
                'image_path' => 'img-bakmi/nasi-goreng-biasa-uritan.jpg'
            ],
            [
                'name' => 'Nasi Goreng Magelang Spesial', 
                'description' => 'Nasi Goreng Magelang versi porsi spesial ekstra. Perpaduan nasi, mi, potongan sayuran, dibumbui dengan resep rahasia bumbu ulek Mas Agus dan kecap premium, lalu dimasak dengan teknik api besar anglo arang untuk menghasilkan kelezatan maksimal.', 
                'price' => 35000, 
                'category' => 'Menu Spesial', 
                'is_available' => true,
                'image_path' => 'img-bakmi/nasi-goreng-magelang-spesial.jpg'
            ],
            [
                'name' => 'Rica-Rica Spesial', 
                'description' => 'Kombinasi bagian-bagian terbaik dari daging ayam kampung, jeroan, dan tulangan muda yang ditumis bersama dalam bumbu rica-rica racikan rahasia dapur Mas Agus. Sangat pedas, harum kemangi, dan memiliki bumbu pekat yang memikat.', 
                'price' => 35000, 
                'category' => 'Menu Spesial', 
                'is_available' => true,
                'image_path' => 'img-bakmi/rica-rica-spesial.jpg'
            ],

            // MINUMAN
            [
                'name' => 'Wedang Ronde', 
                'description' => 'Minuman kebugaran tradisional hangat yang terbuat dari seduhan jahe emprit asli dan gula aren. Disajikan lengkap dengan bola-bola ketan kenyal berisi tumbukan kacang tanah manis, kolang-kaling, dan taburan kacang tanah sangrai.', 
                'price' => 12000, 
                'category' => 'Minuman', 
                'is_available' => true,
                'image_path' => 'img-bakmi/wedang-ronde.jpg'
            ],
            [
                'name' => 'Wedang Jahe', 
                'description' => 'Seduhan air jahe murni yang dibakar terlebih dahulu sebelum digeprek, dimasak bersama gula batu atau gula jawa. Memberikan sensasi rasa hangat yang kuat di tenggorokan dan berkhasiat menyegarkan tubuh setelah lelah beraktivitas.', 
                'price' => 7000, 
                'category' => 'Minuman', 
                'is_available' => true,
                'image_path' => 'img-bakmi/wedang-jahe.jpg'
            ],
            [
                'name' => 'Susu Jahe', 
                'description' => 'Kombinasi harmonis antara kehangatan ekstrak jahe murni bakar dengan gurihnya susu kental manis putih premium. Minuman hangat berenergi yang sangat pas dinikmati untuk menemani makan malam sepiring bakmi jawa panas.', 
                'price' => 9000, 
                'category' => 'Minuman', 
                'is_available' => true,
                'image_path' => 'img-bakmi/susu-jahe.jpg'
            ],
            [
                'name' => 'Wedang Teh', 
                'description' => 'Teh tubruk khas Jawa Tengah beraroma melati yang diseduh panas-panas menggunakan teko tanah liat. Menggunakan pemanis gula batu asli, menciptakan cita rasa teh yang wasgitel (wangi, panas, legi, kenthel).', 
                'price' => 5000, 
                'category' => 'Minuman', 
                'is_available' => true,
                'image_path' => 'img-bakmi/wedang-teh.jpg'
            ],
            [
                'name' => 'Es Teh Manis', 
                'description' => 'Minuman segar legendaris dari daun teh melati pilihan yang diseduh pekat, dipadukan dengan gula cair murni dan disajikan dingin dengan es batu melimpah. Sangat efektif membasuh rasa pedas setelah menyantap rica-rica.', 
                'price' => 5000, 
                'category' => 'Minuman', 
                'is_available' => true,
                'image_path' => 'img-bakmi/es-teh-manis.jpg'
            ],
            [
                'name' => 'Es Teh Tawar', 
                'description' => 'Seduhan daun teh melati pekat murni yang disajikan dingin dengan es batu tanpa tambahan pemanis atau gula sama sekali. Pilihan minuman segar yang netral, sehat, dan mengedepankan aroma asli daun teh.', 
                'price' => 2000, 
                'category' => 'Minuman', 
                'is_available' => true,
                'image_path' => 'img-bakmi/es-teh-tawar.jpg'
            ],
            [
                'name' => 'Teh Tawar', 
                'description' => 'Seduhan teh tubruk melati hangat tanpa gula. Memiliki rasa sedikit sepet yang khas dan aroma melati yang menenangkan, berfungsi sebagai pembersih langit-langit mulut yang sempurna sehabis menikmati makanan berminyak.', 
                'price' => 3000, 
                'category' => 'Minuman', 
                'is_available' => true,
                'image_path' => 'img-bakmi/teh-tawar.jpg'
            ],
            [
                'name' => 'Wedang Jeruk', 
                'description' => 'Minuman jeruk hangat yang dibuat dari perasan buah jeruk keprok segar pilihan asli, dipadukan dengan sirup gula pasir murni. Kaya akan vitamin C dengan keseimbangan rasa asam-manis alami yang menyehatkan lambung.', 
                'price' => 8000, 
                'category' => 'Minuman', 
                'is_available' => true,
                'image_path' => 'img-bakmi/wedang-jeruk.jpg'
            ],
            [
                'name' => 'Es Jeruk', 
                'description' => 'Perasan murni buah jeruk segar berpadu dengan es batu kristal dan pemanis gula alami. Minuman dingin buah asli yang menawarkan kesegaran maksimal, sangat cocok diminum di tengah hangatnya suasana warung bakmi.', 
                'price' => 8000, 
                'category' => 'Minuman', 
                'is_available' => true,
                'image_path' => 'img-bakmi/es-jeruk.jpg'
            ],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}
