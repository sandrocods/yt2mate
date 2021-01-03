<?php
require_once '../lib/Class_downloader.php';
if((!isset($_GET['link'])) || empty($_GET['link'])){

    print_r(json_encode([
        'Error' => 'Link Youtube cannot empty'
     ]));

}else{
    $YTdownload = new Ytdownload();

    $info = $YTdownload->Analyze($_GET['link']);

    if ($info['Status'] === true) {
        $data = [
            1 => $info['Data']['k_data_vid'],
            2 => $info['Data']['k__id'],
            //3 => $info['Data']['f-quality'][$answer_quality]
        ];

        // Download Default Quality 128 As MP3
        $download = $YTdownload->Getlink($data[1], $data[2] , '128');

        print_r(json_encode([
            'Title' => $info['Data']['Title'],
            'Link' => $download['Data']['Link']
        ]));

    } else {
        print_r(json_encode([
            'Error' => $info['Data']
        ]));
    }
}
