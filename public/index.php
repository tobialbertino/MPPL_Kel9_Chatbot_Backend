<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
$pass_signature = true;

// set LINE channel_access_token and channel_secret
$channel_access_token = "+EQsMJuVKhcS8WIQPG+uiRxv1+y3H56Y47MiZhaC9kGJhcEZu9tMZIR4LHfIBRJWwRB2k4h7emF1s/pAC1RHrG8J3mHx5TfxcBb6W5jusn9wTv8skV5kBAEMwDYjIk1TjIM3o5uacxSSO1NIGsF/swdB04t89/1O/w1cDnyilFU=";
$channel_secret = "e89ad4f3a0e93f37c30012413ca30c67";

// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);

$app = AppFactory::create();
$app->setBasePath("/public");

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello World!");
    return $response;
});

// buat route untuk webhook
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) {
    // get request body and line signature header
    $body = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');

    // log body and signature
    file_put_contents('php://stderr', 'Body: ' . $body);

    if ($pass_signature === false) {
        // is LINE_SIGNATURE exists in request header?
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }

        // is this request comes from LINE?
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }

    $data = json_decode($body, true);
    if (is_array($data['events'])) {
        foreach ($data['events'] as $event) {
            if ($event['type'] == 'message') {
                //reply message
                if ($event['message']['type'] == 'text') {
                    if (strtolower($event['message']['text']) == 'user id') {

                        $result = $bot->replyText($event['replyToken'], $event['source']['userId']);

                    } elseif (strtolower($event['message']['text']) == 'ipb maps') {

                        $flexTemplate = file_get_contents("../peta.json"); // template flex message
                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    "type" => "template",
                                    "altText" => "IPB Maps",
                                    "template" => json_decode($flexTemplate)
                                ]
                            ],
                        ]);

                    } elseif (strtolower($event['message']['text']) == 'info lomba' or strtolower($event['message']['text']) == 'info event') {

                        $flexTemplate = file_get_contents("../lomba.json"); // template flex message
                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    "type" => "template",
                                    "altText" => "Info Lomba/Event",
                                    "template" => json_decode($flexTemplate)
                                ]
                            ],
                        ]);

                    } elseif (strtolower($event['message']['text']) == 'biaya' or strtolower($event['message']['text']) == 'ukt' or strtolower($event['message']['text']) == 'harga' or strtolower($event['message']['text']) == 'biaya kuliah') {

                        $result = $bot->replyText($event['replyToken'], 'Biaya kuliah di IPB menggunakan sistem UKT (Uang Kuliah Tunggal), tahun lalu mengikuti ketentuan yang dapat dilihat pada link: admisi.ipb.ac.id/biayaperkuliahan. Untuk tahun ini, biaya perkuliahan belum ditetapkan, Anda bisa memantau pada tautan yang tercantum di atas untuk informasi lebih lanjut.');

                    } elseif (strtolower($event['message']['text']) == 'jurusan' or strtolower($event['message']['text']) == 'program studi' or strtolower($event['message']['text']) == 'daya tampung' or strtolower($event['message']['text']) == 'program sarjana' or strtolower($event['message']['text']) == 'jurusan s1') {
                        
                        $result = $bot->replyText($event['replyToken'], 'Daftar jurusan / Program studi yang ada di IPB, beserta daya tampung program sarjana dan keketatannya dapat dilihat di https://admisi.ipb.ac.id/daya-tampung-program-sarjana/');

                    } elseif (strtolower($event['message']['text']) == 'akreditas' or strtolower($event['message']['text']) == 'akreditasi' or strtolower($event['message']['text']) == 'akreditasi program sarjana' or strtolower($event['message']['text']) == 'akre') {
                    
                        $result = $bot->replyText($event['replyToken'], 'Untuk daftar akreditasi program sarjana S1 dapat dilihat di https://kmmai.ipb.ac.id/status-akreditasi/program-sarjana-s1/');
                    
                    } elseif (strtolower($event['message']['text']) == 'asrama' or strtolower($event['message']['text']) == 'asrama putra' or strtolower($event['message']['text']) == 'asrama putri') {

                        $result = $bot->replyText($event['replyToken'], 'Asrama PPKU IPB merupakan unit pendukung kegiatan belajar mahasiswa yang berupa pembinaan keasramaan yang berada di dalam lingkungan kampus IPB dan dikhususkan bagi mahasiswa tingkat pertama IPB program sarjana.
                        
Terdapat 10 gedung asrama di IPB, dengan rincian sebagai berikut.
5 gedung asrama putri : A1, A2, A3, A4, A5
5 gedung asrama putra : C1, C2, C3, C4(Sylvalestari), C5(Sylvasari)');                 
                    
                    } elseif (strtolower($event['message']['text']) == 'jalur kuliah' or strtolower($event['message']['text']) == 'jalur masuk' or strtolower($event['message']['text']) == 'seleksi' or strtolower($event['message']['text']) == 'seleksi masuk') {

                        $result = $bot->replyText($event['replyToken'], 'Seleksi masuk Sekolah Vokasi :
1. Undangan Seleksi Masuk IPB (USMI)
2. Ujian Tulis Mandiri (UTM)
3. Beasiswa Utusan Daerah (BUD)

Seleksi masuk Program Sarjana :
*Jalur Masuk Reguler*
1. SNMPTN
2. SBMPTN
3. UTM Berbasis Komputer Program Sarjana
4. Prestasi Internasional Nasional (PIN)
5. Ketua Osis
6. BUD
*Jalur Masuk Internasional*
1. Kelas Internasional WNI
2. Kelas Internasional WNA');
                    } elseif (strtolower($event['message']['text']) == 'alamat' or strtolower($event['message']['text']) == 'alamat kampus' or strtolower($event['message']['text']) == 'lokasi' or strtolower($event['message']['text']) == 'lokasi kampus') {
                    
                        $LocationMessageBuilder1 = new LocationMessageBuilder('IPB Dramaga', 'Kampus IPB, Jl. Raya Dramaga, Babakan, Kec. Dramaga, Kota Bogor, Jawa Barat 16680', -6.5540699, 106.7234745);
                        $LocationMessageBuilder2 = new LocationMessageBuilder('IPB Baranangsiang', 'Kampus IPB Baranangsiang, Jalan Pajajaran Raya No.1, Bogor Tengah, Kota Bogor, RT.02/RW.05, Tegalleg', -6.6007316, 106.8007649);
                        $LocationMessageBuilder3 = new LocationMessageBuilder('School of Business IPB', 'Gedung SB-IPB Kampus IPB Gunung Gede, Jl. Raya Pajajaran, RT.03/RW.06, Babakan, Kecamatan Bogor Teng', -6.586787, 106.806078);
                        $LocationMessageBuilder4 = new LocationMessageBuilder('IPB Bogor Cilibende', 'Jl. Lodaya II No.9-11, RT.02/RW.06, Babakan, Kecamatan Bogor Tengah, Kota Bogor, Jawa Barat 16128', -6.5888655, 106.805715);
                        $LocationMessageBuilder5 = new LocationMessageBuilder('IPB Sukabumi', '3WH7+V2V, Benteng, Warudoyong, Sukabumi, West Java 43132', -6.9202471, 106.9104806);
                        
                        $multiMessageBuilder = new MultiMessageBuilder();
                        $multiMessageBuilder->add($LocationMessageBuilder1);
                        $multiMessageBuilder->add($LocationMessageBuilder2);
                        $multiMessageBuilder->add($LocationMessageBuilder3);
                        $multiMessageBuilder->add($LocationMessageBuilder4);
                        $multiMessageBuilder->add($LocationMessageBuilder5);
                        
                        $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);
                    } elseif (strtolower($event['message']['text']) == 'help' or strtolower($event['message']['text']) == 'daftar kata kunci') {
                    
                        $result = $bot->replyText($event['replyToken'], 'Daftar kata kunci yang tersedia adalah sebagai berikut:
1. IPB Maps : untuk mengetahui peta IPB Dramaga
2. Info lomba / info event : untuk mengetahui lomba atau event apa saja yang akan berlangsung
3. Top 3 faq : untuk mengetahui 3 pertanyaan yang paling sering ditanyakan
4. Bantuan SI : untuk mengetahui sistem-sistem informasi apa saja yang tersedia
5. Biaya / UKT / Harga / Biaya Kuliah : untuk mengetahui biaya perkuliahan di IPB
6. Jurusan / Program studi / Daya tampung / program sarjana / Jurusan s1 : untuk melihat daftar program studi yang terdapat di IPB beserta daya tampungnya
7. Akreditas / Akreditasi / Akreditasi Program Sarjana / Akre : untuk melihat daftar akreditasi program studi sarjana
8. Asrama / Asrama Putra / Asrama Putri : untuk mengetahui informasi mengenai asrama IPB
9. Jalur kuliah / Jalur masuk / seleksi / seleksi masuk : untuk mengetahui jalur masuk apa saja yang tersedia
10. Alamat / Alamat Kampus / Lokasi / Lokasi Kampus : untuk mengetahui informasi mengenai alamat berbagai kampus IPB
11. Singkatan / nama tempat / singkatan tempat : untuk mengetahui informasi mengenai singkatan nama tempat
12. Hi / hii / hai / hei / halo / hallo / hello / helo : untuk menyapa bot
13. Help / Daftar kata kunci : untuk meihat daftar kata kunci yang tersedia

Notes : Untuk kata kunci yang terdapat "/" harap pilih salah satu saja dan penginputan kata kunci tidak case sensitive');
                    } elseif (strtolower($event['message']['text']) == 'top 3 faq') {
                        $textMessageBuilder1 = new TextMessageBuilder('1. Q: Apakah ada beasiswa untuk mahasiswa baru di IPB?
A: Ada, contohnya adalah bidikmisi. Untuk info beasiswa lebih lanjut dapat memasukkan kata kunci "beasiswa"');
                        $textMessageBuilder2 = new TextMessageBuilder('2. Q: Bagaimana dengan biaya perkuliahan untuk S1?
A: Biaya kuliah di IPB menggunakan sistem UKT (Uang Kuliah Tunggal), tahun lalu mengikuti ketentuan yang dapat dilihat pada link: admisi.ipb.ac.id/biayaperkuliahan. Untuk tahun ini,biaya perkuliahan belum ditetapkan, Anda bisa memantau pada tautan yang tercantum diatas untuk informasi lebih lanjut.');
                        $textMessageBuilder3 = new TextMessageBuilder('3. Q: Jurusan apa di IPB yang sepi peminat, tapi yang peluang kerjanya banyak?
A: Tidak ada jurusan yang sepi peminat di IPB.  Semua jurusan rata-rata menerima 1 (satu) orang dari 10 pelamar. Peluang kerja bukan ditentukan oleh jurusan Anda, namun kompetensi Anda adalah kuncinya.');
                        
                        $multiMessageBuilder = new MultiMessageBuilder();
                        $multiMessageBuilder->add($textMessageBuilder1);
                        $multiMessageBuilder->add($textMessageBuilder2);
                        $multiMessageBuilder->add($textMessageBuilder3);

                        $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);

                    } elseif (strtolower($event['message']['text']) == 'bantuan si') {

                        $flexTemplate = file_get_contents("../bantuan.json"); // template flex message
                        $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                            'replyToken' => $event['replyToken'],
                            'messages'   => [
                                [
                                    "type" => "template",
                                    "altText" => "Bantuan SI",
                                    "template" => json_decode($flexTemplate)
                                ]
                            ],
                        ]); 
                    } elseif (strtolower($event['message']['text']) == 'singkatan' or strtolower($event['message']['text']) == 'nama tempat' or strtolower($event['message']['text']) == 'singkatan tempat') { 
                        $result = $bot->replyText($event['replyToken'], 'Ada beberapa singkatan nama tempat yang umum digunakan oleh mahasiswa IPB, diantaranya yaitu:
1. Kortan (Koridor Tanah) di FAPERTA
2. Korpin (Koridor Pinus) di FAPERTA
3. GC (Kantin Green Corner) di dekat Agrimart 1
4. BC (Kantin Blue Corner) di dekat FPIK
5. RC (Kantin Red Corner) di dekat Asrama Putra
6. Tedung (Kantin Tenda Ungu) di dekat Asrama Putri
7. YC (Kantin Yellow Corner) di belakang GWW');
                    } elseif (strtolower($event['message']['text']) == 'hi' or strtolower($event['message']['text']) == 'hii' or strtolower($event['message']['text']) == 'hai' or strtolower($event['message']['text']) == 'hei' or strtolower($event['message']['text']) == 'halo' or strtolower($event['message']['text']) == 'hallo' or strtolower($event['message']['text']) == 'hello' or strtolower($event['message']['text']) == 'helo') {
                        
                        $result = $bot->replyText($event['replyToken'], 'Hello, kami adalah IPB-bot, atau bisa disebut Ibot yang akan membantu Anda untuk mendapatkan informasi seputar kampus IPB.
            
Untuk mengetahui daftar kata kunci yang dapat digunakan, Anda bisa kirimkan “help” atau “daftar kata kunci”');
                    }
                        else {

                        $result = $bot->replyText($event['replyToken'], 'Mohon maaf kata kunci yang Anda masukkan tidak tersedia, masukkan kata kunci "daftar kata kunci" atau "help" untuk melihat daftar kata kunci apa saja yang tersedia.');
                    
                    }


                    // or we can use replyMessage() instead to send reply message
                    // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);


                    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                } //content api
                elseif (
                    $event['message']['type'] == 'image' or
                    $event['message']['type'] == 'video' or
                    $event['message']['type'] == 'audio' or
                    $event['message']['type'] == 'file'
                ) {
                    $contentURL = " https://example.herokuapp.com/public/content/" . $event['message']['id'];
                    $contentType = ucfirst($event['message']['type']);
                    $result = $bot->replyText($event['replyToken'],
                        $contentType . " yang Anda kirim bisa diakses dari link:\n " . $contentURL);

                    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                } //group room
                elseif (
                    $event['source']['type'] == 'group' or
                    $event['source']['type'] == 'room'
                ) {
                    //message from group / room
                    if ($event['source']['userId']) {

                        $userId = $event['source']['userId'];
                        $getprofile = $bot->getProfile($userId);
                        $profile = $getprofile->getJSONDecodedBody();
                        $greetings = new TextMessageBuilder("Halo, " . $profile['displayName']);

                        $result = $bot->replyMessage($event['replyToken'], $greetings);
                        $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                        return $response
                            ->withHeader('Content-Type', 'application/json')
                            ->withStatus($result->getHTTPStatus());
                    }
                } else {
                    //message from single user
                    $result = $bot->replyText($event['replyToken'], $event['message']['text']);
                    $response->getBody()->write((string)$result->getJSONDecodedBody());
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                }
            }
        }
        return $response->withStatus(200, 'for Webhook!'); //buat ngasih response 200 ke pas verify webhook
    }
    return $response->withStatus(400, 'No event sent!');
});

$app->get('/content/{messageId}', function ($req, $response, $args) use ($bot) {
    // get message content

    $messageId = $args['messageId'];
    $result = $bot->getMessageContent($messageId);

    // set response
    $response->getBody()->write($result->getRawBody());

    return $response
        ->withHeader('Content-Type', $result->getHeader('Content-Type'))
        ->withStatus($result->getHTTPStatus());
});

$app->get('/pushmessage', function ($req, $response) use ($bot) {
    // send push message to user
    $userId = 'Isi dengan user ID Anda';
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push');
    $result = $bot->pushMessage($userId, $textMessageBuilder);

    $response->getBody()->write("Pesan push berhasil dikirim!");
    return $response
        //->withHeader('Content-Type', 'application/json') //baris ini dapat dihilangkan karena hanya menampilkan pesan di browser
        ->withStatus($result->getHTTPStatus());
});

$app->get('/multicast', function ($req, $response) use ($bot) {
    // list of users
    $userList = [
        'Isi dengan user ID Anda',
        'Isi dengan user ID teman1',
        'Isi dengan user ID teman2',
        'dst'
    ];

    // send multicast message to user
    $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan multicast');
    $result = $bot->multicast($userList, $textMessageBuilder);


    $response->getBody()->write("Pesan multicast berhasil dikirim!");
    return $response
        //->withHeader('Content-Type', 'application/json') //baris ini dapat dihilangkan karena hanya menampilkan pesan di browser
        ->withStatus($result->getHTTPStatus());
});

$app->get('/profile/{userId}', function ($req, $response, $args) use ($bot) {
    // get user profile
    $userId = $args['userId'];
    $result = $bot->getProfile($userId);

    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($result->getHTTPStatus());
});

$app->run();




