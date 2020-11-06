<?php
$token = "xxxxx";//ganti dengan token bot kamu (lihat di @BotFather)
$tg = "https://api.telegram.org/bot".$token;
$updates = json_decode(file_get_contents("php://input"),true);

//kondisi
if(isset($updates['message']['text'])){//kalau ada pesan teks masuk
    $pesan = $updates['message']['text'];
    $pesan = str_replace("'","\'",$pesan);
    $find = array("َ","ِ","ُ","ً","ٍ","ٌ","ْ","ّ");
    $pesan = str_replace($find,"",$pesan);
    $length = strlen($pesan);
    $first_name = $updates['message']['chat']['first_name'];
    isset($updates['message']['chat']['last_name'])?$last_name=['message']['chat']['last_name']:$last_name='';
    $name = $first_name.' '.$last_name;
    $username = $updates['message']['chat']['username'];
    $chat_id = $updates['message']['chat']['id'];
    $message_id = $updates['message']['message_id'];
    
    //start
    if($pesan == "/start"){
        //kalau pesannya "/start"
        sedangMengetik();
        $pesan_balik = "Assalamualaikum {$name}\n\nSelamat datang di bot Cari Hadis. Silahkan tulis kata atau kalimat yang ingin anda cari.";
    }elseif($pesan == "/kitab"){
        //kalau pesannya "/kitab"
        sedangMengetik();
        $get = json_decode(file_get_contents("http://api.carihadis.com"),true);
        $count = count($get['kitab']);
        if($count>0){
            $pesan_balik = "Tersedia {$count} kitab:\n";
            for($x=0;$x<$count;$x++){
            $kitab = $get['kitab'][$x];
            $pesan_balik .= $x + 1 .". /{$kitab}\n";
            }
        }
    }elseif($pesan == "/keterangan"){
            $pesan_balik = "Keterangan\n\n1. Mesin akan mencari kata kunci yang anda masukkan, tanpa melihat apa karakter sebelumnya atau sesudahnya. Misalnya, jika anda memasukkan kata kunci \"makan\", maka mesin akan mencari kata \"makan\", \"memakan\", \"dimakan\", \"disamakan\", \"makanan\" dan sebagainya.\n\n2. Urutan kata menentukan hasil pencarian. Misalnya, \"kaki dan tangan\" akan memberikan hasil yang berbeda dengan \"tangan dan kaki\".\n\n3. Untuk pencarian menggunakan kata kunci berbahasa Arab, mesin akan membedakan antara hamzah washol (ا) dan hamzah qotho (أ إ ء).";
    }elseif(preg_match("/^\/__([a-zA-Z_]+)__(\d+)/",$pesan,$ke)){
        //kalau format pesannya /__Nama_Kitab__123 (bernomor)
        sedangMengetik();
        $kitab = $ke[1];
        $id = $ke[2];
        $get = json_decode(file_get_contents("http://api.carihadis.com/?kitab=".$kitab."&id=".$id),true);
        $count = count($get['data']);
        if($count>0){
            $id = $get['data'][1]['id'];
            $nass = $get['data'][1]['nass'];
            $terjemah = $get['data'][1]['terjemah'];
            $pesan_balik = "{$nass}\n{$terjemah} (<a href='https://carihadis.com/{$kitab}/{$id}'>{$kitab}: {$id}</a>)";
        }else{
            $pesan_balik = "Data tidak ditemukan. Periksa nama kitab dan nomor secara benar.";
        }
        
    }elseif(preg_match('/^\/([a-zA-Z_]+)(\d+)?/',$pesan,$ke)){
        //kalau format pesannya /Nama_Kitab (tanpa nomor) atau /Nama_Kitab123 (bernomor)
        sedangMengetik();
        $kitab = $ke[1];
        if(!isset($ke[2])){
            $get = json_decode(file_get_contents("http://api.carihadis.com/?kitab=".$kitab."&id=1"),true);
        }else{
            $get = json_decode(file_get_contents("http://api.carihadis.com/?kitab=".$kitab."&id=".$ke[2]),true);
        }
        $count = count($get['data']);
        if($count>0){
            $id = $get['data'][1]['id'];
            $nass = $get['data'][1]['nass'];
            $terjemah = $get['data'][1]['terjemah'];
            $pesan_balik = "{$nass}\n{$terjemah} (<a href='https://carihadis.com/{$kitab}/{$id}'>{$kitab}: {$id}</a>)";
        }else{
            $pesan_balik = "Data tidak ditemukan. Periksa nama kitab dan nomor secara benar.";
        }
    }
    else{
        //kalau pesannya teks bebas
        sedangMengetik();
        $get = json_decode(file_get_contents("http://api.carihadis.com/?q=".urlencode($pesan)),true);
        $count = count($get['data']);
        if($count>0){//kalau ada hasil
            $pesan_balik = "Anda mencari: ".$pesan."\n\nDitemukan hasil sebagai berikut:\n\n";
            $x=0;
            while($x<$count){
                $kitab = $get['data'][$x]['kitab'];
                $id = $get['data'][$x]['id'];
                $jml = count($id);
                $x++;
                $i=0;
                while($i<$jml){
                    $pesan_balik .= "/__".$kitab."__".$id[$i]."\n";
                    $i++;
                }
            }
            $pesan_balik .= "\nSilahkan tekan link di atas untuk membukanya.";
        }else{
            //kalau tidak ada hasil
            $pesan_balik = "Tidak ditemukan hasil.";
        }
        
    }
    kirim($pesan_balik,$chat_id,$message_id);
}


    //fungsi2
    function kirim($pesan_balik,$chat_id,$message_id){
        global $tg;
        if(strlen($pesan_balik)<4096){
            file_get_contents($tg."/sendMessage?parse_mode=HTML&text=".urlencode($pesan_balik)."&chat_id=".$chat_id."&reply_to_message_id=".$message_id);
        }else{
            $array = potong($pesan_balik,4096);
            foreach($array as $potongan){
                file_get_contents($tg."/sendMessage?parse_mode=HTML&text=".urlencode($potongan)."&chat_id=".$chat_id."&reply_to_message_id=".$message_id);
            }
        }
        
    }

    function sedangMengetik(){
        global $tg;
        global $chat_id;
        file_get_contents($tg."/sendChatAction?action=typing&chat_id=".$chat_id);
    }

    function pecah($text,$jml_kar){
        $karakter = $text{$jml_kar};
        while($karakter != ' ' AND $karakter != "\n" AND $karakter != "\r" AND $karakter != "\r\n") {//kalau bukan spasi atau new line
            $karakter = $text{--$jml_kar};//cari spasi sebelumnya
        }
        $pecahan = substr($text, 0, $jml_kar);
        return trim($pecahan);
    }

    function potong($text,$jml_kar){
        $panjang = strlen($text);
        $ke = 0;
        $pecahan = [];
        while($panjang>$jml_kar){
            $pecahan[] = pecah($text,$jml_kar);//str
            $panjang = strlen($pecahan[$ke]);//int
            $text = trim(substr($text,$panjang));//str
            $panjang = strlen($text);//int
            $ke++;//int
        }
        $array = array_merge($pecahan, array($text));
        return $array;
    }

?>